<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventReminders;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setAccessType('offline');
    }

    /**
     * Set the user's access token and refresh if needed.
     */
    protected function authenticate(User $user)
    {
        if (!$user->google_token) {
            return false;
        }

        $this->client->setAccessToken($user->google_token);

        if ($this->client->isAccessTokenExpired()) {
            if ($user->google_refresh_token) {
                try {
                    $newAccessToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    
                    if (isset($newAccessToken['error'])) {
                        Log::error('Google Token Refresh Error: ' . json_encode($newAccessToken));
                        return false;
                    }

                    $user->update([
                        'google_token' => json_encode($newAccessToken), // Save full token array just in case
                        // 'google_refresh_token' might not be returned again, so we keep the old one
                    ]);
                    
                    $this->client->setAccessToken($newAccessToken);
                } catch (\Exception $e) {
                    Log::error('Google Refresh Exception: ' . $e->getMessage());
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Create, Update or Delete a Google Calendar Event for a Task.
     */
    public function syncTaskEvent(Task $task)
    {
        /** @var User $user */
        $user = $task->creator;
        
        if (!$user || !$this->authenticate($user)) {
            return 'auth_failed';
        }

        $service = new Calendar($this->client);

        // CASE: start_date was removed
        if (!$task->start_date) {
            if ($task->google_calendar_event_id) {
                $this->deleteEvent($task);
                return 'deleted';
            }
            return 'no_date';
        }
        
        // Prepare Event Data
        $summary = $task->title;
        $description = ($task->description ?? 'No description') . "\n\n--- \nView Task: " . config('app.url') . "/task/" . $task->id;
        
        // Use Start Date (and time if available)
        $startDateTime = $task->start_date->format(\DateTime::RFC3339);
        // Default end time is 1 hour later if no deadline
        $endDateTime = $task->deadline 
            ? ($task->deadline > $task->start_date ? $task->deadline->format(\DateTime::RFC3339) : $task->start_date->addHours(1)->format(\DateTime::RFC3339))
            : $task->start_date->addHours(1)->format(\DateTime::RFC3339);

        $eventData = [
            'summary' => $summary,
            'description' => $description,
            'start' => new EventDateTime(['dateTime' => $startDateTime]),
            'end' => new EventDateTime(['dateTime' => $endDateTime]),
            'reminders' => new EventReminders([
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 60], // 1 hour reminder
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ]),
        ];

        $event = new Event($eventData);

        try {
            if ($task->google_calendar_event_id) {
                try {
                    $service->events->update('primary', $task->google_calendar_event_id, $event);
                    return 'updated';
                } catch (\Google\Service\Exception $e) {
                    if ($e->getCode() == 404) {
                        $newEvent = $service->events->insert('primary', $event);
                        $task->update(['google_calendar_event_id' => $newEvent->id]);
                        return 'recreated';
                    }
                    throw $e;
                }
            } else {
                $newEvent = $service->events->insert('primary', $event);
                $task->update(['google_calendar_event_id' => $newEvent->id]);
                return 'created';
            }
        } catch (\Exception $e) {
            Log::error('Google Calendar Sync Failed: ' . $e->getMessage());
            return 'failed';
        }
    }

    public function deleteEvent(Task $task)
    {
        if (!$task->google_calendar_event_id || !$task->creator) {
            return;
        }

        if (!$this->authenticate($task->creator)) {
            return;
        }

        $service = new Calendar($this->client);
        try {
            $service->events->delete('primary', $task->google_calendar_event_id);
            $task->update(['google_calendar_event_id' => null]); // Clean up
        } catch (\Exception $e) {
            Log::error('Google Calendar Delete Failed: ' . $e->getMessage());
            // Ignore 404 or 410 (already gone)
        }
    }
}
