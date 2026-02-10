<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use App\Models\User;
use App\Models\Task;
use App\Models\GoogleCalendarEvent;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected $client;

    public function __construct($accessToken)
    {
        $client = new Client();
        $client->setAccessToken($accessToken);
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        // Only request the minimal scope required to create/manage events
        // (https://www.googleapis.com/auth/calendar.events)
        $client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client = $client;
        $this->service = new Calendar($client);
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
                    
                    // If Google returns an error object, log non-sensitive parts only
                    if (isset($newToken['error'])) {
                        $err = $newToken['error'] ?? 'unknown_error';
                        $desc = $newToken['error_description'] ?? '';
                        Log::error("GCal Service: Refresh error for User #{$user->id}: {$err} {$desc}");
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
     * Main sync logic to handle create/update/delete for ANY workspace member.
     * Tailored for shared hosting (handles recreations cleanly).
     */
    public function handleSync(Task $task, string $action, ?string $oldEventId = null, ?int $oldUserId = null)
    {
        try {
            // Case 1: Task Deleted
            if ($action === 'deleted') {
                if ($oldUserId && $oldEventId) {
                    $user = User::find($oldUserId);
                    if ($user) {
                        $this->deleteEvent($user, $oldEventId);
                        GoogleCalendarEvent::where('task_id', $task->id)
                            ->where('user_id', $oldUserId)
                            ->delete();
                    }
                } else {
                    $events = GoogleCalendarEvent::where('task_id', $task->id)->get();
                    foreach ($events as $gEvent) {
                        /** @var GoogleCalendarEvent $gEvent */
                        if ($gEvent->user) {
                            $this->deleteEvent($gEvent->user, $gEvent->google_event_id);
                        }
                        $gEvent->delete();
                    }
                }
                return;
            }

            // Case 2: Date changes / Removal
            if (!$task->start_date || !$task->deadline) {
                $events = GoogleCalendarEvent::where('task_id', $task->id)->get();
                foreach ($events as $gEvent) {
                    /** @var GoogleCalendarEvent $gEvent */
                    if ($gEvent->user) {
                        $this->deleteEvent($gEvent->user, $gEvent->google_event_id);
                    }
                    $gEvent->delete();
                }
                return;
            }

            // Case 3: Global Sync for Members
            $workspace = $task->board?->plan?->workspace;
            if (!$workspace) return;

            $allIntegratedMembers = $workspace->members()->whereNotNull('google_token')->get();
            
            $intendedUsers = $allIntegratedMembers;
            if (!$task->is_public) {
                $involvedIds = [$task->created_by, $task->assigned_to, $task->working_by_id];
                $intendedUsers = $allIntegratedMembers->filter(fn($u) => in_array($u->id, $involvedIds));
            }
            
            $intendedUserIds = $intendedUsers->pluck('id')->toArray();

            // Cleanup old members
            $ghostEvents = GoogleCalendarEvent::where('task_id', $task->id)
                ->whereNotIn('user_id', $intendedUserIds)
                ->get();
                
            foreach ($ghostEvents as $gEvent) {
                /** @var GoogleCalendarEvent $gEvent */
                if ($gEvent->user) {
                    $this->deleteEvent($gEvent->user, $gEvent->google_event_id);
                }
                $gEvent->delete();
            }

            // Push/Update for intended members
            foreach ($intendedUsers as $user) {
                try {
                    $existing = GoogleCalendarEvent::where('task_id', $task->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($existing) {
                        $this->deleteEvent($user, $existing->google_event_id);
                        $existing->delete();
                    }

                    if ($this->authenticateForUser($user)) {
                        $service = new Calendar($this->client);
                        $eventData = $this->mapTaskToEvent($task);
                        $createdEvent = $service->events->insert('primary', $eventData);
                        
                        GoogleCalendarEvent::create([
                            'task_id' => $task->id,
                            'user_id' => $user->id,
                            'google_event_id' => $createdEvent->getId()
                        ]);
                    }
                } catch (\Exception $userEx) {
                    $msg = $userEx->getMessage();
                    // Clean up JSON error if present
                    if ($jsonStart = strpos($msg, '{')) {
                        $jsonErr = json_decode(substr($msg, $jsonStart), true);
                        if (isset($jsonErr['error']['message'])) {
                             $msg = $jsonErr['error']['message'];
                        }
                    }
                    
                    Log::error("GCal Sync Failed for Member #{$user->id}: " . $msg);
                    
                    if (str_contains($msg, 'insufficientPermissions') || 
                        str_contains($msg, 'ACCESS_TOKEN_SCOPE_INSUFFICIENT') ||
                        $userEx->getCode() == 403 || 
                        $userEx->getCode() == 401) {
                        
                        $user->updateQuietly([
                            'google_calendar_scopes_granted' => false,
                            'google_calendar_error' => 'Insufficient permissions. Please reconnect.'
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("GCal Global Sync Error: " . $e->getMessage());
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
