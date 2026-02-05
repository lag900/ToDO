<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\storeTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        $workspaceId = $request->query('workspace_id');

        // Feature: Refresh Logic - Optimized to not run on every poll
        if ($request->query('refresh') === 'true') {
            if ($workspaceId && $workspaceId !== 'all') {
                 Task::whereHas('board.plan', function ($q) use ($workspaceId) {
                    $q->where('workspace_id', $workspaceId);
                 })
                 ->where('status', 'testing')
                 ->where('is_reviewed', true)
                 ->update(['status' => 'done']);
            } elseif ($workspaceId === 'all') {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $workspaceIds = $user->workspaces()->pluck('workspaces.id')->toArray();
                Task::whereHas('board.plan', function ($q) use ($workspaceIds) {
                    $q->whereIn('workspace_id', $workspaceIds);
                 })
                 ->where('status', 'testing')
                 ->where('is_reviewed', true)
                 ->update(['status' => 'done']);
            }
        }
        
        // Fix: Support Board View Structure directly
        if ($request->has('grouped') || $request->query('view') === 'board') {
             return response()->json([
                 'boards' => $this->taskService->getWorkspaceBoardsWithTasks($workspaceId)
             ]);
        }
        
        return response()->json($this->taskService->getAllTasks($workspaceId));
    }

    public function show($id)
    {
        return response()->json($this->taskService->getTaskDetails($id));
    }

    public function store(storeTaskRequest $request)
    {
        $validated = $request->validated();
        $task = $this->taskService->createTask($validated);
        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'deadline' => 'sometimes|nullable',
            'start_date' => 'sometimes|nullable',
            'is_reviewed' => 'sometimes|boolean',
            'is_public' => 'sometimes|boolean'
        ]);

        $task = $this->taskService->updateTask($task, $validated);
        return response()->json($task);
    }

    public function addChecklist(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:255'
        ]);

        $item = $this->taskService->addChecklistItem($task, $validated['content']);
        return response()->json($item, 201);
    }

    public function toggleChecklist($itemId)
    {
        $item = $this->taskService->toggleChecklistItem($itemId);
        return response()->json($item);
    }

    public function destroy(Request $request, Task $task)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $workspace = $task->board->plan->workspace;

        // Check if user is creator
        $isCreator = $task->created_by === $user->id;

        // Check if user is workspace owner or admin
        $isWorkspaceAdmin = $workspace->owner_id === $user->id || 
            $user->workspaces()
                 ->where('workspaces.id', $workspace->id)
                 ->wherePivot('role', 'owner')
                 ->exists();

        if (!$isCreator && !$isWorkspaceAdmin) {
            return response()->json(['message' => 'Unauthorized to delete this task'], 403);
        }

        $this->taskService->deleteTask($task);
        return response()->json(null, 204);
    }
}
