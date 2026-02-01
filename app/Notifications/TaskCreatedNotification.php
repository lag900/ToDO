<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCreatedNotification extends Notification implements ShouldQueue
{   
    use Queueable;

    protected $task;

    public function __construct(\App\Models\Task $task)
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $workspaceName = $this->task->project?->workspace?->name ?? 'THINKER';
        $creatorName = $this->task->creator?->display_name ?? 'Someone';
        $priorityLabel = strtoupper($this->task->priority ?? 'MEDIUM');
        
    return (new MailMessage)
            ->subject("New Task Added in [$workspaceName]")
            ->view('emails.tasks.created', [
                'task' => $this->task,
                'workspaceName' => $workspaceName,
                'creatorName' => $creatorName,
                'priorityLabel' => $priorityLabel,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
        ];
    }
}
