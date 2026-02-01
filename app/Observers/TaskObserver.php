<?php

namespace App\Observers;

use App\Mail\emailTaskNotification;
use App\Models\Task;
use App\Models\User;
use App\Models\EmailLog;
use App\Notifications\TaskCreatedNotification;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\Notification;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        // Get the workspace through board -> plan
        $workspace = $task->board?->plan?->workspace;
        if (!$workspace) return;

        // Get all members of the workspace
        $members = $workspace->members()->get();

        foreach ($members as $member)   {
            $settings = $member->notification_settings ?? [
                'email_enabled' => true, 
                'types' => ['task_created'],
                'exclude_self' => false
            ];

            // 1. Check if email notifications are enabled globally for user
            if (!($settings['email_enabled'] ?? false)) continue;

            // 2. Check if 'task_created' is enabled for user
            if (!in_array('task_created', $settings['types'] ?? [])) continue;

            // 3. Exclude self if configured
            if (($settings['exclude_self'] ?? false) && $member->id === $task->created_by) continue;

            // Send notification
            // $member->notify(new TaskCreatedNotification($task));
            FacadesMail::to($member->email)->send(new emailTaskNotification($task));

            // Log the email (this doesn't check if mail actually sent, but it's the "log intent")
            EmailLog::create([
                'task_id' => $task->id,
                'sender_id' => $task->created_by,
                'receiver_id' => $member->id,
                'type' => 'task_created',
                'sent_at' => now()
            ]);
        }
    }
}
