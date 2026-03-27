<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogTaskActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $taskId,
        protected ?int $userId,
        protected string $action,
        protected array $changes = [],
        protected ?string $taskName = null,
        protected ?string $userEmail = null,
        protected ?int $workspaceId = null,
    ) {}

    public function handle(): void
    {
        $task = Task::find($this->taskId);
        if (!$task) return;

        $task->activityLogs()->create([
            'user_id'      => $this->userId,
            'action'       => $this->action,
            'changes'      => $this->changes,
            'task_name'    => $this->taskName,
            'user_email'   => $this->userEmail,
            'workspace_id' => $this->workspaceId,
        ]);
    }
}
