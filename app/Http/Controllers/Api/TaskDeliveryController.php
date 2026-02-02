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
use App\Events\UpdatedTask;

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

        return DB::transaction(function () use ($task, $workspace, $validated) {
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

            broadcast(new UpdatedTask($task->id, $workspace->id, 'updated'))->toOthers();

            return $delivery->load(['items', 'user']);
        });
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'message' => 'No file received. The file might exceed the server\'s upload_max_filesize (currently 30MB) or post_max_size limits.'
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|max:30720', // 30MB
        ]);

        $path = $request->file('file')->store('deliveries', 'public');
        
        return response()->json([
            'path' => Storage::disk('public')->url($path),
            'name' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function destroy(TaskDelivery $delivery)
    {
        $task = $delivery->task;
        $workspace = $task->board->plan->workspace;
        
        // Permission check: Owner of delivery or Owner of workspace
        if ($delivery->user_id !== Auth::id() && $workspace->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to delete this delivery.'], 403);
        }

        return DB::transaction(function () use ($delivery, $task, $workspace) {
            // Delete actual files from storage if they exist
            foreach ($delivery->items as $item) {
                if ($item->type !== 'link') {
                    // Extract path from URL if it's a full URL
                    $path = str_replace(url('/storage/'), '', $item->content);
                    $path = ltrim($path, '/'); // Ensure no leading slash for disk operations
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }
            
            $delivery->items()->delete();
            $delivery->delete();
            
            broadcast(new UpdatedTask($task->id, $workspace->id, 'updated'))->toOthers();

            return response()->json(['message' => 'Delivery deleted successfully.']);
        });
    }

    public function showFile(Request $request, $filename)
    {
        $path = 'deliveries/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path);

        // Files that should open in the browser for preview
        $previewable = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'text/plain',
        ];

        // If 'download' parameter is present, force attachment
        $disposition = ($request->has('download') || !in_array($mime, $previewable)) ? 'attachment' : 'inline';

        return response()->file($file, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
