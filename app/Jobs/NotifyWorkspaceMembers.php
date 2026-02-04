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

class NotifyWorkspaceMembers
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
    public function handle(): void
    {
        try {
            $task = $this->task->fresh(['board.plan.workspace', 'creator']); 
            if (!$task) return;

            $workspace = $task->board->plan->workspace;
            $members = $workspace->members()->get();

            Log::info("Notification Process [{$this->type}] for Task #{$task->id}. Assigned To: " . ($task->assigned_to ?? 'NONE'));

            foreach ($members as $user) {
                // RULE 0: Exclude the user who performed the action (Self-action exclusion)
                if ($this->actor && $user->id === $this->actor->id) {
                    continue;
                }

                $settings = $user->notification_settings ?? [
                    'email_enabled' => true,
                    'types' => ['task_created', 'task_updated', 'task_completed'],
                    'exclude_self' => false
                ];
                
                // standard settings checks
                if (!isset($settings['email_enabled']) || !$settings['email_enabled']) continue;
                if (!isset($settings['types']) || !in_array($this->type, $settings['types'])) continue;

                /**
                 * RULE 1: Assigned vs Unassigned Logic
                 */
                $isTarget = false;

                if ($task->assigned_to) {
                    // If assigned, ONLY the assignee gets notified.
                    if ($user->id == $task->assigned_to) {
                        $isTarget = true;
                    }
                    
                    // Special case for updates: maybe notify the creator too? 
                    // User said: "Creator (اختياري حسب الإعداد)" - for now, let's keep it strictly to assignee if assigned.
                } else {
                    // If unassigned, EVERYONE in the team gets notified.
                    $isTarget = true;
                }

                if ($isTarget && $user->email) {
                    Log::info("Dispatching email to: {$user->email} for action: {$this->type}");
                    $mailable = match ($this->type) {
                        'task_created' => new \App\Mail\TaskCreatedNotification($task),
                        'task_updated' => new \App\Mail\TaskUpdatedNotification($task, $this->changes, $this->actor),
                        'task_completed' => new \App\Mail\TaskCompletedNotification($task, $this->actor),
                        default => null
                    };

                    if ($mailable) {
                        Mail::to($user->email)->send($mailable);
                        
                        // Log the notification in the activity log
                        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
                            'task_id' => $task->id,
                            'user_id' => $user->id,
                            'action' => 'email_notification_sent',
                            'changes' => json_encode(['type' => $this->type, 'recipient' => $user->email]),
                            'task_name' => $task->title,
                            'user_email' => $user->email,
                            'workspace_id' => $workspace->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Notification Job failed [{$this->type}]: " . $e->getMessage());
        }
    }
}
