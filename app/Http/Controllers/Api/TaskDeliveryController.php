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
        $role = $membership ? $membership->pivot->role : null;

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

            // Status is intentionally NOT changed here.
            // Deliveries/Attachments are independent of task status.

            $delivery->load(['items', 'user']);

            // Notify workspace members about the new delivery (asynchronously)
            \App\Jobs\NotifyWorkspaceMembers::dispatch($task, 'task_delivered', [], Auth::user())->afterResponse();

            return $delivery;
        });
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'message' => 'No file received.'
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|max:51200', // Allow up to 50MB
        ]);

        $file = $request->file('file');
        $path = $file->store('deliveries', 'public');
        
        // Dispatch heavy processing to queue
        if (str_contains($file->getMimeType(), 'image/')) {
            \App\Jobs\ProcessUploadedImage::dispatch($path);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return response()->json([
            'path' => $disk->url($path),
            'name' => $file->getClientOriginalName(),
        ]);
    }

    public function destroy(TaskDelivery $delivery)
    {
        $task = $delivery->task;
        $workspace = $task->board->plan->workspace;
        
        $isWorkspaceOwner = $workspace->members()
            ->where('user_id', Auth::id())
            ->where('role', 'owner')
            ->exists();
            
        // Permission check: Owner of delivery or Owner of workspace
        if ($delivery->user_id !== Auth::id() && !$isWorkspaceOwner) {
            return response()->json(['message' => 'Unauthorized to delete this delivery.'], 403);
        }

        return DB::transaction(function () use ($delivery) {
            // Delete actual files from storage if they exist
            foreach ($delivery->items as $item) {
                if ($item->type !== 'link') {
                    // Extract path from URL if it's a full URL
                    $path = str_replace(url('/storage/'), '', $item->content);
                    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                    $disk = Storage::disk('public');
                    if ($disk->exists($path)) {
                        $disk->delete($path);
                    }
                }
            }
            
            $delivery->items()->delete();
            $delivery->delete();
            
            return response()->json(['message' => 'Delivery deleted successfully.']);
        });
    }

    public function showFile(Request $request, $filename)
    {
        $path = 'deliveries/' . $filename;
        
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        
        if (!$disk->exists($path)) {
            abort(404);
        }

        $file = $disk->path($path);
        $mime = $disk->mimeType($path);

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

    public function updateItem(Request $request, TaskDeliveryItem $item)
    {
        $delivery = $item->delivery;
        $workspace = $delivery->task->board->plan->workspace;
        
        $isWorkspaceOwner = $workspace->members()
            ->where('user_id', Auth::id())
            ->where('role', 'owner')
            ->exists();
            
        if ($delivery->user_id !== Auth::id() && !$isWorkspaceOwner) {
            return response()->json(['message' => 'Unauthorized to update this attachment.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item->update(['name' => $validated['name']]);

        return response()->json($item);
    }
}
