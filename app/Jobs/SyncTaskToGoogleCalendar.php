<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTaskToGoogleCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tries count for robustness.
     */
    public $tries = 2;

    protected $taskId;
    protected $action;
    protected $oldEventId;
    protected $oldUserId;

    /**
     * Create a new job instance.
     */
    public function __construct($taskId, string $action, ?string $oldEventId = null, ?int $oldUserId = null)
    {
        $this->taskId = $taskId;
        $this->action = $action;
        $this->oldEventId = $oldEventId;
        $this->oldUserId = $oldUserId;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleCalendarService $service): void
    {
        // Try to find the task, including trashed if we are deleting
        $task = Task::withTrashed()->find($this->taskId);
        
        if (!$task) return;

        $service->handleSync($task, $this->action, $this->oldEventId, $this->oldUserId);
    }
}
