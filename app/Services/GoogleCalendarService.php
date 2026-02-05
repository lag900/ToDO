<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        // Initialize Google Client with standard config
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Authenticate the client for a specific user.
     * Handles automatic token refresh if expired or about to expire.
     */
    protected function authenticateForUser(User $user): bool
    {
        if (empty($user->google_token)) {
            return false;
        }

        $this->client->setAccessToken($user->google_token);

        // Logic check: Is it expired?
        $isExpired = $this->client->isAccessTokenExpired();
        
        // Bonus: If we have an expires_at in DB, use it for preemptive refresh
        if (!$isExpired && $user->google_token_expires_at) {
            if ($user->google_token_expires_at->isPast() || $user->google_token_expires_at->diffInMinutes(now()) < 5) {
                $isExpired = true;
            }
        }

        if ($isExpired) {
            if ($user->google_refresh_token) {
                try {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    
                    if (isset($newToken['error'])) {
                        Log::error("GCal Service: Refresh error for User #{$user->id}: " . json_encode($newToken));
                        // If error is 'invalid_grant', the refresh token is revoked
                        if (isset($newToken['error']) && $newToken['error'] === 'invalid_grant') {
                             $user->updateQuietly(['google_calendar_scopes_granted' => false]);
                        }
                        return false;
                    }

                    $expiresIn = $newToken['expires_in'] ?? 3600;
                    
                    // Save new tokens
                    $user->update([
                        'google_token' => $newToken['access_token'],
                        'google_token_expires_at' => now()->addSeconds($expiresIn)
                    ]);
                    
                    $this->client->setAccessToken($newToken['access_token']);
                } catch (\Exception $e) {
                    Log::error("GCal Service: Failed to refresh token for User #{$user->id}: " . $e->getMessage());
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Main sync logic to handle create/update/delete.
     * Tailored for shared hosting (handles recreations cleanly).
     */
    public function handleSync(Task $task, string $action, ?string $oldEventId = null, ?int $oldUserId = null)
    {
        try {
            // Case 1: Task Deleted (or being moved away from a user)
            if ($action === 'deleted') {
                $targetUserId = $oldUserId ?: ($task->working_by_id ?: $task->assigned_to ?: $task->created_by);
                $targetEventId = $oldEventId ?: $task->google_calendar_event_id;

                if ($targetUserId && $targetEventId) {
                    $user = User::find($targetUserId);
                    if ($user) $this->deleteEvent($user, $targetEventId);
                }
                return;
            }

            // Case 2: Create or Update
            // Rule: We only sync if there is a start_date and deadline
            if (!$task->start_date || !$task->deadline) {
                if ($task->google_calendar_event_id) {
                    $user = $task->workingBy ?: $task->assignee ?: $task->creator;
                    if ($user) $this->deleteEvent($user, $task->google_calendar_event_id);
                    $task->updateQuietly(['google_calendar_event_id' => null]);
                }
                return;
            }

            $user = $task->workingBy ?: $task->assignee ?: $task->creator;
            if (!$user || !$user->google_token) return;

            // If updating, we delete the old one first to prevent duplicates/ghosts
            if ($task->google_calendar_event_id) {
                $this->deleteEvent($user, $task->google_calendar_event_id);
                $task->updateQuietly(['google_calendar_event_id' => null]);
            }

            // Create new event
            if ($this->authenticateForUser($user)) {
                $service = new Calendar($this->client);
                $eventData = $this->mapTaskToEvent($task);
                
                $createdEvent = $service->events->insert('primary', $eventData);
                
                $task->updateQuietly([
                    'google_calendar_event_id' => $createdEvent->getId()
                ]);
            }

        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            // Check for Insufficient Permissions (Google Scope issue)
            if (str_contains($message, 'insufficientPermissions') || $e->getCode() == 403) {
                Log::error("GCal Sync: Permission Denied for User #{$user->id}. Marking as scope error.");
                $user->updateQuietly([
                    'google_calendar_error' => 'Insufficient scopes. Please reconnect your Google account.',
                    'google_calendar_scopes_granted' => false
                ]);
                throw new \App\Exceptions\GoogleCalendarReconnectException($message);
            } else {
                Log::error("GCal Service Sync Error: " . $message);
            }
        }
    }

    /**
     * Prepare Google Event object from Task
     */
    protected function mapTaskToEvent(Task $task): Event
    {
        $desc = $task->description ?: 'No description provided.';
        $desc .= "\n\n--- Shared via Todo Batucore ---";

        return new Event([
            'summary' => '📌 ' . $task->title,
            'description' => $desc,
            'start' => [
                'dateTime' => $task->start_date->toRfc3339String(),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
            'end' => [
                'dateTime' => $task->deadline->toRfc3339String(),
                'timeZone' => config('app.timezone', 'UTC'),
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 60],
                    ['method' => 'popup', 'minutes' => 15],
                ],
            ],
        ]);
    }

    /**
     * Delete an event safely
     */
    public function deleteEvent(User $user, string $eventId): bool
    {
        if (!$this->authenticateForUser($user)) return false;

        try {
            $service = new Calendar($this->client);
            $service->events->delete('primary', $eventId);
            return true;
        } catch (\Exception $e) {
            // 404/410 means it's already gone, which is fine
            if (in_array($e->getCode(), [404, 410])) return true;
            Log::error("GCal Service: Delete failed for event {$eventId}: " . $e->getMessage());
            return false;
        }
    }
}
