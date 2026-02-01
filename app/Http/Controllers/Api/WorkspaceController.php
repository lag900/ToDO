<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Workspace;
use App\Models\Plan;
use App\Models\Board;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkspaceController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return response()->json($user->workspaces()->with(['plans.boards', 'members'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Personal,Work,Team,Company',
            'intent' => 'nullable|string',
            'settings' => 'nullable|array'
        ]);

        $workspace = Workspace::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'intent' => $validated['intent'],
            'settings' => $validated['settings'] ?? [
                'enable_priorities' => true,
                'enable_due_dates' => true,
                'use_default_statuses' => true
            ],
            'owner_id' => Auth::id()
        ]);

        // Attach owner as a member
        $workspace->members()->attach(Auth::id(), ['role' => 'owner']);

        // Create a default Plan for this workspace
        $plan = Plan::create([
            'name' => $validated['name'] . ' Roadmap',
            'workspace_id' => $workspace->id,
            'user_id' => Auth::id()
        ]);

        // Create a default Board for this Plan
        Board::create([
            'name' => 'Main Board',
            'plan_id' => $plan->id,
            'user_id' => Auth::id()
        ]);

        return response()->json($workspace->load('plans.boards'), 201);
    }
    public function destroy($id)
    {
        try {
            $workspace = Workspace::findOrFail($id);

            if (intval($workspace->owner_id) !== intval(Auth::id())) {
                return response()->json(['message' => 'Only the owner can delete this workspace.'], 403);
            }

            $workspace->delete();

            return response()->json(['message' => 'Workspace deleted.']);
        } catch (\Exception $e) {
            Log::error('Workspace deletion failed: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Internal server error during deletion.'], 500);
        }
    }
}
