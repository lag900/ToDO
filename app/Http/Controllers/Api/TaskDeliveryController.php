<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskDelivery;
use App\Models\TaskDeliveryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskDeliveryController extends Controller
{
    public function store(Request $request, Task $task)
    {
        // Permission check
        $workspace = $task->board->plan->workspace;
        $membership = $workspace->members()->where('users.id', Auth::id())->first();
        $role = $membership ? $membership->pivot->role : ($workspace->owner_id === Auth::id() ? 'owner' : null);

        if (!$role || $role === 'viewer') {
            return response()->json(['message' => 'Unauthorized to deliver tasks.'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:file,image,link',
            'items.*.name' => 'required|string',
            'items.*.content' => 'required|string', // URL or base64 or path
            'items.*.description' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($task, $validated) {
            $delivery = TaskDelivery::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'notes' => $validated['notes']
            ]);

            foreach ($validated['items'] as $itemData) {
                TaskDeliveryItem::create([
                    'task_delivery_id' => $delivery->id,
                    'type' => $itemData['type'],
                    'name' => $itemData['name'],
                    'content' => $itemData['content'],
                    'description' => $itemData['description'] ?? null
                ]);
            }

            // Move task to Done if not already
            if ($task->status !== 'done') {
                $task->update(['status' => 'done']);
            }

            return $delivery->load(['items', 'user']);
        });
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $path = $request->file('file')->store('deliveries', 'public');
        
        return response()->json([
            'path' => Storage::url($path),
            'name' => $request->file('file')->getClientOriginalName(),
        ]);
    }
}
