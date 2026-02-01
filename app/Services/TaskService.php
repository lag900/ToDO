<?php

namespace App\Services;

use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\Board;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Get all tasks for a specific workspace.
     */
    public function getAllTasks($workspaceId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Global View (All My Workspaces)
        if ($workspaceId === 'all') {
            $workspaceIds = $user->workspaces()->pluck('workspaces.id');
            return Task::with(['assignee', 'assignedBy', 'board.plan.workspace', 'creator', 'subtasks.creator', 'workingBy', 'deliveries.items', 'deliveries.user'])
                ->whereNull('parent_id')
                ->whereHas('board.plan', function ($q) use ($workspaceIds) {
                    $q->whereIn('workspace_id', $workspaceIds);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if (!$workspaceId) {
            return collect();
        }

        // 2. Context View (Specific Workspace)
        // Checks if user is in workspace OR is an Admin (Fix for empty views)
        $isInWorkspace = $user->workspaces()->where('workspaces.id', $workspaceId)->exists();
        if (!$isInWorkspace && !$user->hasRole('admin')) {
             return collect();
        }

        return Task::with(['assignee', 'assignedBy', 'board.plan.workspace', 'creator', 'subtasks.creator', 'workingBy', 'deliveries.items', 'deliveries.user'])
            ->whereNull('parent_id')
            ->whereHas('board.plan', function ($q) use ($workspaceId) {
                $q->where('workspace_id', $workspaceId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTaskDetails($id)
    {
        $task = Task::with(['assignee', 'assignedBy', 'board.plan.workspace', 'creator', 'subtasks.assignee', 'subtasks.creator', 'checklists', 'activityLogs.user', 'workingBy', 'deliveries.items', 'deliveries.user'])
            ->findOrFail($id);

        // Verify isolation
        $workspaceId = $task->board->plan->workspace_id;
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $isInWorkspace = $user->workspaces()->where('workspaces.id', $workspaceId)->exists();
        if (!$isInWorkspace && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized access to task context.');
        }

        return $task;
    }

    public function createTask(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            
            // Validate board ownership/access
            $boardId = $data['board_id'] ?? null;
            if ($boardId) {
                $board = Board::with('plan')->findOrFail($boardId);
                $workspaceId = $board->plan->workspace_id;
                
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $role = $this->getWorkspaceRole($user, $workspaceId);
                
                // Allow if user has role OR is admin
                if ((!$role || $role === 'viewer') && !$user->hasRole('admin')) {
                    abort(403, 'You do not have permission to create tasks in this workspace.');
                }
            }

            // If creating a subtask for a done task, reopen the parent
            if (isset($data['parent_id'])) {
                $parent = Task::find($data['parent_id']);
                if ($parent && $parent->status === 'done') {
                    $this->handleAutoReopen($parent, 'new sub-task added');
                }
            }

            if (isset($data['assigned_to'])) {
                $data['assigned_by_id'] = Auth::id();
            }

            $task = Task::create($data);

            $this->logActivity($task, 'created', ['title' => $task->title]);

            if ($task->assigned_to) {
                $this->logActivity($task, 'assigned_task', [
                    'to' => $task->assignee->display_name,
                    'by' => Auth::user()?->display_name
                ]);
            }

            return $task->load(['assignee', 'assignedBy', 'board.plan', 'creator', 'workingBy']);

        });
    }

    public function updateTask(Task $task, array $data)
    {
        return DB::transaction(function () use ($task, $data) {
            $workspaceId = $task->board->plan->workspace_id;
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $role = $this->getWorkspaceRole($user, $workspaceId);
            
            // Allow if has role OR is admin
            if (!$role && !$user->hasRole('admin')) {
                abort(403, 'You do not have permission to access this workspace.');
            }

            // Viewer can ONLY change status and assigned_to (Admins bypass this)
            if ($role === 'viewer' && !$user->hasRole('admin')) {
                $allowedViewerFields = ['status', 'assigned_to'];
                $attemptedFields = array_keys($data);
                if (count(array_diff($attemptedFields, $allowedViewerFields)) > 0) {
                    abort(403, 'Viewers can only change task status or assign themselves.');
                }
            }

            $oldStatus = $task->status;
            $oldTitle = $task->title;
            $oldAssignedTo = $task->assigned_to;


            // Smart logic: if task is 'done' and we are changing something other than status to 'done' reopen it.
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

            // Handle workingBy logic
            if (isset($data['status'])) {
                if ($data['status'] === 'in_progress') {
                    $data['working_by_id'] = Auth::id();
                } elseif ($task->status === 'in_progress' && $data['status'] !== 'in_progress') {
                    $data['working_by_id'] = null;
                }
            }

            // Handle assigned_by_id logic
            if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $oldAssignedTo) {
                $data['assigned_by_id'] = Auth::id();
            }

            $task->update($data);

            if ($isReopening) {
                $this->logActivity($task, 'reopened', ['reason' => 'Task edited while in completed state']);
            }

            if (isset($data['status']) && $data['status'] !== $oldStatus && !$isReopening) {
                $this->logActivity($task, 'updated_status', [
                    'from' => $oldStatus, 
                    'to' => $data['status'],
                    'actor_email' => Auth::user()?->email
                ]);
                
                // Track sub-task completion if this is a subtask
                if ($data['status'] === 'done' && $task->parent_id) {
                    $this->logActivity($task, 'subtask_completed', ['title' => $task->title]);
                }
            }


            if (isset($data['title']) && $data['title'] !== $oldTitle) {
                $this->logActivity($task, 'updated_title', ['from' => $oldTitle, 'to' => $data['title']]);
            }

            // New: Log other field changes
            $fieldsToLog = ['priority', 'description', 'deadline', 'start_date'];
            foreach ($fieldsToLog as $field) {
                if (isset($data[$field]) && $data[$field] !== $task->getOriginal($field)) {
                    $this->logActivity($task, 'updated_' . $field, [
                        'from' => $task->getOriginal($field), 
                        'to' => $data[$field]
                    ]);
                }
            }

            if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $oldAssignedTo) {
                $assigneeName = $task->assignee ? $task->assignee->display_name : 'Unassigned';
                $this->logActivity($task, 'assigned_task', [
                    'to' => $assigneeName,
                    'by' => Auth::user()?->display_name
                ]);
            }


            return $task->load(['assignee', 'assignedBy', 'board.plan', 'creator', 'subtasks.creator', 'workingBy']);
        });
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
            $role = $this->getWorkspaceRole($user, $workspaceId);
            
            if (!$role || $role === 'viewer') {
                abort(403, 'You do not have permission to delete tasks in this workspace.');
            }

            $this->logActivity($task, 'deleted', ['title' => $task->title]);

            $task->checklists()->delete();
            $task->subtasks()->update(['parent_id' => null]);
            return $task->delete();
        });
    }

    private function logActivity(Task $task, string $action, array $changes = [])
    {
        $log = ActivityLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'task_name' => $task->title,
            'workspace_id' => $task->board?->plan?->workspace_id,
            'action' => $action,
            'changes' => $changes
        ]);


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
        // 1. Get all boards for this workspace
        // We need to fetch boards linked to plans in this workspace
        $boards = Board::whereHas('plan', function($q) use ($workspaceId) {
            $q->where('workspace_id', $workspaceId);
        })->with(['tasks' => function($q) {
             // Load tasks for each board
             // Crucial: ensure no strict status filtering excludes 'todo' tasks
             $q->with(['assignee', 'creator', 'subtasks', 'checklists', 'workingBy'])
               ->orderBy('priority', 'desc')
               ->orderBy('created_at', 'desc');
        }])->get();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
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
