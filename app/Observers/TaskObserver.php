<?php

namespace App\Observers;

use App\Mail\emailTaskNotification;
use App\Models\Task;
use App\Models\User;
use App\Models\EmailLog;
use App\Notifications\TaskCreatedNotification;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->notifyRecipients($task, 'task_created');
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // If start_date was just set or changed, we might want to notify
        if ($task->wasChanged('start_date') && $task->start_date) {
            $this->notifyRecipients($task, 'task_updated');
        }
    }

    protected function notifyRecipients(Task $task, string $type): void
    {
        // Get the workspace through board -> plan
        $workspace = $task->board?->plan?->workspace;
        if (!$workspace) return;

        // Determine who gets the notification
        // If assigned to someone, only they get it. Otherwise all workspace members.
        if ($task->assigned_to) {
            $recipients = User::where('id', $task->assigned_to)->get();
        } else {
            $recipients = $workspace->members()->get();
        }

        foreach ($recipients as $recipient) {
            $settings = $recipient->notification_settings ?? [
                'email_enabled' => true, 
                'types' => ['task_created', 'task_updated'],
                'exclude_self' => false
            ];

            // 1. Check if email notifications are enabled globally for user
            if (!($settings['email_enabled'] ?? false)) continue;

            // 2. Check if the type is enabled for user
            if (!in_array($type, $settings['types'] ?? [])) continue;

            // 3. Exclude self if configured
            if (($settings['exclude_self'] ?? false) && Auth::id() === $recipient->id) continue;

            $mail = new emailTaskNotification($task);

            $startDate = $task->start_date ? \Illuminate\Support\Carbon::parse($task->start_date) : null;
            $isFuture = $startDate && $startDate->isFuture();

            // Determine timing
            if ($isFuture) {
                FacadesMail::to($recipient->email)->later($startDate, $mail);
            } else {
                FacadesMail::to($recipient->email)->send($mail);
            }

            // Log the email intent
            EmailLog::create([
                'task_id' => $task->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $recipient->id,
                'type' => $type,
                'sent_at' => $isFuture ? $startDate : now()
            ]);
        }
    }
}
