<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskUpdatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $changes;
    public $actor;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, array $changes, $actor = null)
    {
        $this->task = $task;
        $this->changes = $changes;
        $this->actor = $actor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Task Updated: ' . $this->task->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tasks.updated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
