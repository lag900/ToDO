<?php

namespace App\Services;

use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\Board;
use App\Jobs\NotifyWorkspaceMembers;
use App\Jobs\LogTaskActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService
{
    protected $googleCalendarService;

    public function __construct()
    {
        $this->googleCalendarService = new \App\Services\GoogleCalendarService();
    }

    /**
     * Get all tasks for a specific workspace.
     */
    public function getAllTasks($workspaceId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Global View (All My Workspaces)
        if ($workspaceId === 'all') {
            $workspaceIds = $user->workspaces()->pluck('workspaces.id')->toArray();
            return Task::with(['assignee', 'creator', 'workingBy', 'board.plan.workspace'])
                ->withCount(['deliveries', 'subtasks', 'checklists'])
                ->whereNull('parent_id')
                ->whereHas('board.plan', function ($q) use ($workspaceIds) {
                    $q->whereIn('workspace_id', $workspaceIds);
                })
                ->where(function ($query) use ($user) {
                    $query->where('is_public', true)
                          ->orWhere('assigned_to', $user->id)
                          ->orWhere('created_by', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if (!$workspaceId) {
            return collect();
        }

        // 2. Context View (Specific Workspace)
        $targetWorkspaceId = (int) $workspaceId;
        
        // Security check: Ensure user has access
        $workspace = $user->workspaces()->where('workspaces.id', $targetWorkspaceId)->first();
        
        if (!$workspace) {
             return collect();
        }

        // Reverted to full eager loading to ensure data consistency and visibility
        // Optimized eager loading for context view
        return Task::with(['assignee', 'creator', 'workingBy', 'board.plan.workspace'])
            ->withCount(['deliveries', 'subtasks', 'checklists'])
            ->whereNull('parent_id')
            ->whereHas('board.plan', function ($q) use ($targetWorkspaceId) {
                $q->where('workspace_id', $targetWorkspaceId);
            })
            ->where(function ($query) use ($user) {
                // Visibility Logic
                $query->where('is_public', true)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTaskDetails($id)
    {
        $task = Task::with([
            'assignee', 
            'assignedBy', 
            'board.plan.workspace', 
            'creator', 
            'subtasks.assignee', 
            'subtasks.creator', 
            'checklists', 
            'activityLogs.user', 
            'workingBy', 
            'deliveries.items', 
            'deliveries.user'
        ])
            ->findOrFail($id);

        // Verify isolation
        $workspaceId = $task->board->plan->workspace_id;
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();
        if (!$workspace) {
            abort(403, 'Unauthorized access to task context.');
        }

        return $task;
    }

    public function createTask(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            
            // Parse dates from dd/mm/yyyy if needed
            if (isset($data['deadline'])) {
                $data['deadline'] = $this->parseCustomDate($data['deadline']);
            }
            if (isset($data['start_date'])) {
                $data['start_date'] = $this->parseCustomDate($data['start_date']);
            }
            
            // Critical hierarchy mapping
            $boardId = $data['board_id'] ?? null;
            if ($boardId) {
                $board = Board::with('plan')->findOrFail($boardId);
                $workspaceId = $board->plan->workspace_id;
                
                // Ensure project_id (legacy) is always set to the plan_id
                $data['project_id'] = $board->plan_id;
                
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();
                
                if (!$workspace) {
                    abort(403, 'You do not have permission to create tasks in this workspace.');
                }

                $role = $workspace->pivot->role;
                if ($role === 'viewer') {
                    abort(403, 'Viewers cannot create tasks.');
                }
            }

            if (isset($data['parent_id'])) {
                $parent = Task::find($data['parent_id']);
                if ($parent && $parent->status === 'done') {
                    $this->handleAutoReopen($parent, 'new sub-task added');
                }
            }

            if (isset($data['assigned_to']) && !empty($data['assigned_to'])) {
                $data['assigned_by_id'] = Auth::id();
            }

            // Visibility Logic: Default to public for workspace/team visibility
            if (!isset($data['is_public'])) {
                $data['is_public'] = true;
            }

            // Create the task with enforced mappings
            $task = Task::create($data);
            $task->load(['creator', 'assignee']);

            $this->logActivity($task, 'created', ['title' => $task->title]);

            if ($task->assigned_to) {
                $task->load('assignee');
                $this->logActivity($task, 'assigned_task', [
                    'to' => $task->assignee->display_name ?? 'Someone',
                    'by' => Auth::user()?->display_name
                ]);
            }

            // Google Calendar Sync and Notifications are handled by TaskObserver to ensure fast responses.
            
            return $task->load(['assignee', 'assignedBy', 'board.plan', 'creator', 'workingBy']);

        });
    }

    public function updateTask(Task $task, array $data)
    {
        $workspaceId = $task->board->plan->workspace_id;
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();

        if (!$workspace) {
            abort(403, 'You do not have permission to access this workspace.');
        }

        $role = $workspace->pivot->role;

        if ($role === 'viewer') {
            $allowedViewerFields = ['status', 'assigned_to'];
            $attemptedFields = array_keys($data);
            if (count(array_diff($attemptedFields, $allowedViewerFields)) > 0) {
                abort(403, 'Viewers can only change task status or assign themselves.');
            }
        }

        // Parse dates
        if (isset($data['deadline'])) {
            $data['deadline'] = $this->parseCustomDate($data['deadline']);
        }
        if (isset($data['start_date'])) {
            $data['start_date'] = $this->parseCustomDate($data['start_date']);
        }

        $oldStatus     = $task->status;
        $oldTitle      = $task->title;
        $oldAssignedTo = $task->assigned_to;

        $isReopening = false;
        if ($task->status === 'done' && (!isset($data['status']) || $data['status'] !== 'done')) {
            $fieldsToCheck = ['title', 'description', 'priority', 'assigned_to', 'deadline', 'start_date'];
            foreach ($fieldsToCheck as $field) {
                if (isset($data[$field]) && $data[$field] !== $task->$field) {
                    $isReopening = true;
                    break;
                }
            }
        }

        if ($isReopening) {
            $data['status'] = 'todo';
        }

        if (isset($data['status'])) {
            if ($data['status'] === 'in_progress') {
                $data['working_by_id'] = Auth::id();
                if (!$task->start_date && !isset($data['start_date'])) {
                    $data['start_date'] = now();
                }
            } elseif ($task->status === 'in_progress' && $data['status'] !== 'in_progress') {
                $data['working_by_id'] = null;
            }
        }

        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $oldAssignedTo) {
            $data['assigned_by_id'] = Auth::id();
        }

        // --- Fast path: just update and return ---
        $task->update($data);

        // --- Dispatch all logging / notifications as background jobs ---
        $taskId      = $task->id;
        $taskName    = $task->title;
        $userEmail   = $user->email;
        $userId      = $user->id;

        if ($isReopening) {
            LogTaskActivity::dispatch($taskId, $userId, 'reopened', ['reason' => 'Task edited while in completed state'], $taskName, $userEmail, $workspaceId);
        }

        if (isset($data['status']) && $data['status'] !== $oldStatus && !$isReopening) {
            LogTaskActivity::dispatch($taskId, $userId, 'updated_status', ['from' => $oldStatus, 'to' => $data['status'], 'actor_email' => $userEmail], $taskName, $userEmail, $workspaceId);
            if ($data['status'] === 'done' && $task->parent_id) {
                LogTaskActivity::dispatch($taskId, $userId, 'subtask_completed', ['title' => $task->title], $taskName, $userEmail, $workspaceId);
            }
        }

        if (isset($data['title']) && $data['title'] !== $oldTitle) {
            LogTaskActivity::dispatch($taskId, $userId, 'updated_title', ['from' => $oldTitle, 'to' => $data['title']], $taskName, $userEmail, $workspaceId);
        }

        $fieldsToLog = ['priority', 'description', 'deadline', 'start_date', 'is_public'];
        foreach ($fieldsToLog as $field) {
            if (array_key_exists($field, $data)) {
                $oldVal = $task->getOriginal($field);
                $newVal = $task->$field;
                $hasChanged = false;
                if ($oldVal instanceof \Carbon\Carbon && $newVal instanceof \Carbon\Carbon) {
                    $hasChanged = !$oldVal->equalTo($newVal);
                } else {
                    $hasChanged = $oldVal != $newVal;
                }
                if ($hasChanged) {
                    LogTaskActivity::dispatch($taskId, $userId, 'updated_' . $field, [
                        'from' => $oldVal ? (is_string($oldVal) ? $oldVal : $oldVal->format('d/m/Y H:i')) : null,
                        'to'   => $newVal ? (is_string($newVal) ? $newVal : $newVal->format('d/m/Y H:i')) : null,
                    ], $taskName, $userEmail, $workspaceId);
                }
            }
        }

        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $oldAssignedTo) {
            $task->load('assignee');
            $assigneeName = $task->assignee ? $task->assignee->display_name : 'Unassigned';
            LogTaskActivity::dispatch($taskId, $userId, 'assigned_task', ['to' => $assigneeName, 'by' => $user->display_name], $taskName, $userEmail, $workspaceId);
        }

        return $task;
    }

    public function addChecklistItem(Task $task, string $content)
    {
        return DB::transaction(function () use ($task, $content) {
            if ($task->status === 'done') {
                $this->handleAutoReopen($task, 'new checklist item added');
            }

            $item = $task->checklists()->create(['content' => $content]);
            
            $this->logActivity($task, 'added_checklist_item', ['content' => $content]);

            return $item;

        });
    }

    public function toggleChecklistItem($itemId)
    {
        return DB::transaction(function () use ($itemId) {
            $item = \App\Models\Checklist::findOrFail($itemId);
            $task = $item->task;

            if (!$item->is_completed && $task->status === 'done') {
                $this->handleAutoReopen($task, 'checklist item uncompleted');
            }

            $item->is_completed = !$item->is_completed;
            $item->save();

            $this->logActivity($task, $item->is_completed ? 'completed_checklist_item' : 'uncompleted_checklist_item', ['content' => $item->content]);

            return $item;

        });
    }

    public function deleteTask(Task $task)
    {
        return DB::transaction(function () use ($task) {
            // Isolation & Permission check
            $workspaceId = $task->board->plan->workspace_id;
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();
            
            if (!$workspace || $workspace->pivot->role === 'viewer') {
                abort(403, 'You do not have permission to delete tasks in this workspace.');
            }

            $this->logActivity($task, 'deleted', ['title' => $task->title]);

            // Google Calendar removal is handled by TaskObserver.

            $task->checklists()->delete();
            $task->subtasks()->update(['parent_id' => null]);
            return $task->delete();
        });
    }

    protected function logActivity(Task $task, $action, $changes = [])
    {
        $user = Auth::user();
        $task->activityLogs()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'changes' => $changes,
            'task_name' => $task->title,
            'user_email' => $user?->email,
            'workspace_id' => $task->board?->plan?->workspace_id,
        ]);
    }

    protected function parseCustomDate($dateString)
    {
        if (!$dateString) return null;
        
        // Try dd/mm/yyyy first
        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $dateString);
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y H:i', $dateString);
            } catch (\Exception $e2) {
                try {
                    return \Carbon\Carbon::createFromFormat('d/m/Y', $dateString);
                } catch (\Exception $e3) {
                    // Fallback to standard parsing
                    return \Carbon\Carbon::parse($dateString);
                }
            }
        }
    }

    private function handleAutoReopen(Task $task, string $reason)
    {
        $task->update(['status' => 'todo']);
        $this->logActivity($task, 'reopened', ['reason' => "Task re-opened due to $reason"]);
    }


    protected function getWorkspaceRole($user, $workspaceId)
    {
        $workspaceUser = DB::table('workspace_user')
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->first();

        return $workspaceUser ? $workspaceUser->role : null;
    }

    /**
     * Get Boards with grouped tasks for UI
     */
    public function getWorkspaceBoardsWithTasks($workspaceId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Get all boards for this workspace
        // We need to fetch boards linked to plans in this workspace
        $boards = Board::whereHas('plan', function($q) use ($workspaceId) {
            $q->where('workspace_id', $workspaceId);
        })->with(['tasks' => function($q) use ($user) {
             $q->where(function($query) use ($user) {
                 $query->where('assigned_to', $user->id)
                       ->orWhere('created_by', $user->id)
                       ->orWhere('is_public', true);
             })
             ->with(['assignee', 'creator', 'subtasks', 'checklists', 'workingBy', 'deliveries'])
             ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low') ASC")
             ->orderBy('created_at', 'desc');
        }])->get();

        
        // Optional: Filter boards if needed based on permissions, 
        // but generally boards are visible to workspace members.
        
        // 2. Format response to match UI expectations
        // This ensures the frontend receives exactly what it needs to render columns
        return $boards->map(function($board) {
            return [
                'id' => $board->id,
                'name' => $board->name, // e.g. "To Do", "In Progress"
                'tasks' => $board->tasks
            ];
        });
    }
}
