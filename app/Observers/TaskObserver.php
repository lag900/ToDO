<?php

namespace App\Observers;

use App\Models\Task;
use App\Jobs\SyncTaskToGoogleCalendar;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        // 1. Google Calendar Sync
        // Now service handles all workspace members, so we trigger if dates are present
        if ($task->start_date && $task->deadline) {
            $this->dispatchSync($task, 'created');
        }

        // 2. Email Notifications
        \App\Jobs\NotifyWorkspaceMembers::dispatch($task, 'task_created', [], \Illuminate\Support\Facades\Auth::user())->afterResponse();
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // 1. Google Calendar Sync logic
        $calendarFields = ['title', 'description', 'start_date', 'deadline', 'working_by_id', 'assigned_to', 'is_public'];
        
        if ($task->wasChanged($calendarFields)) {
            $this->dispatchSync($task, 'updated');
        }

        // 2. Email Notifications logic
        $actor = \Illuminate\Support\Facades\Auth::user();
        if ($task->wasChanged('status') && $task->status === 'done' && $task->getOriginal('status') !== 'done') {
            \App\Jobs\NotifyWorkspaceMembers::dispatch($task, 'task_completed', [], $actor)->afterResponse();
        } else {
            $significantFields = ['title', 'description', 'priority', 'deadline', 'assigned_to'];
            $changes = [];
            foreach ($significantFields as $field) {
                if ($task->wasChanged($field)) {
                    $changes[$field] = [
                        'from' => $task->getOriginal($field),
                        'to' => $task->$field
                    ];
                }
            }
            if (!empty($changes)) {
                \App\Jobs\NotifyWorkspaceMembers::dispatch($task, 'task_updated', $changes, $actor)->afterResponse();
            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        // Global sync handles deletion for all related users
        $this->dispatchSync($task, 'deleted');
    }

    /**
     * Helper to dispatch sync job.
     * Works perfectly on shared hosting (dispatchAfterResponse).
     */
    protected function dispatchSync(Task $task, string $action, ?string $eventId = null, ?int $userId = null)
    {
        // Using dispatch(...)->afterResponse() ensures the user doesn't wait for Google API in web requests
        // while still satisfying the 'no persistent worker' shared hosting environment.
        SyncTaskToGoogleCalendar::dispatch(
            $task->id, 
            $action, 
            $eventId ?: $task->google_calendar_event_id, 
            $userId
        )->afterResponse();
    }
}
