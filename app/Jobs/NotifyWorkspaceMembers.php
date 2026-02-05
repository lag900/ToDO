<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotifyWorkspaceMembers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    protected $type;
    protected $changes;
    protected $actor;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task, string $type, array $changes = [], User $actor = null)
    {
        $this->task = $task;
        $this->type = $type;
        $this->changes = $changes;
        $this->actor = $actor;
    }

    /**
     * Execute the job.
     */
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $task = $this->task->fresh(['board.plan.workspace', 'creator', 'assignee']); 
            if (!$task) return;

            $workspace = $task->board->plan->workspace;
            $members = $workspace->members()->get();

            Log::info("Notification Process [{$this->type}] for Task #{$task->id}. Actor: " . ($this->actor->id ?? 'System'));

            foreach ($members as $user) {
                if ($this->shouldNotify($user, $task)) {
                    $this->sendNotification($user, $task);
                }
            }
        } catch (\Exception $e) {
            Log::error("Notification Job failed [{$this->type}]: " . $e->getMessage());
        }
    }

    /**
     * Decision Matrix for Notification Logic
     */
    protected function shouldNotify(User $user, Task $task): bool
    {
        $settings = $user->notification_settings ?? [];

        // 1. Global Email Switch (Default: True)
        // If the key is missing, we assume enabled to ensure delivery unless explicitly disabled.
        $emailEnabled = $settings['email_enabled'] ?? true;
        if (!$emailEnabled) {
            return false;
        }

        // 2. Event Type Subscription
        // Default: Subscribe to all major events
        $allowedTypes = $settings['types'] ?? ['task_created', 'task_updated', 'task_completed'];
        if (!in_array($this->type, $allowedTypes)) {
            return false;
        }

        // 3. User Roles relative to Task
        $isActor = ($this->actor && $user->id === $this->actor->id);
        $isAssignee = ($task->assigned_to == $user->id);
        $isCreator = ($task->created_by == $user->id);

        // 4. Actor Exclusion (Exclude my own actions)
        // Default: True (Don't notify me of what I just did)
        $excludeSelf = $settings['exclude_self'] ?? true;
        if ($isActor && $excludeSelf) {
            return false;
        }

        // 5. Creator Exclusion ("Don't notify me for tasks I create myself")
        // Meaning: If I am just the creator (and not the assignee), skip me.
        // Default: False (Creators usually want to know)
        $excludeCreatedByMe = $settings['exclude_tasks_created_by_me'] ?? false;
        
        $interestedAsCreator = $isCreator;
        if ($excludeCreatedByMe) {
            $interestedAsCreator = false;
        }

        // 6. Scope / Targeting Logic
        if ($task->assigned_to) {
            // TARGETED MODE:
            // Notification goes to: Assignee AND Creator (if interested)
            if ($isAssignee || $interestedAsCreator) {
                return true;
            }
            return false;
        } else {
            // BROADCAST MODE (Unassigned):
            // Notification goes to: Everyone in the workspace (Team Responsibility)
            return true;
        }
    }

    protected function sendNotification(User $user, Task $task): void
    {
        Log::info("Dispatching email to: {$user->email} for action: {$this->type}");
        
        $mailable = match ($this->type) {
            'task_created' => new \App\Mail\TaskCreatedNotification($task),
            'task_updated' => new \App\Mail\TaskUpdatedNotification($task, $this->changes, $this->actor),
            'task_completed' => new \App\Mail\TaskCompletedNotification($task, $this->actor),
            default => null
        };

        if ($mailable) {
            Mail::to($user->email)->send($mailable);
            $this->logActivity($user, $task);
        }
    }

    protected function logActivity(User $user, Task $task): void
    {
        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'email_notification_sent',
            'changes' => json_encode(['type' => $this->type, 'recipient' => $user->email]),
            'task_name' => $task->title,
            'user_email' => $user->email,
            'workspace_id' => $task->board->plan->workspace_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

