<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = $request->query('workspace_id');
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($workspaceId) {
            // 1. Check if user is a member of the workspace
            $isWorkspaceMember = $user->workspaces()->where('workspaces.id', $workspaceId)->exists();
            
            if ($isWorkspaceMember) {
                return response()->json(Plan::where('workspace_id', $workspaceId)->with('boards')->get());
            }

            // 2. Check if user has specific shared plans in this workspace
            return response()->json(
                $user->sharedPlans()
                    ->where('workspace_id', $workspaceId)
                    ->with('boards')
                    ->get()
            );
        }

        // 3. Global list: Workspace plans + individually shared plans
        $workspaceIds = $user->workspaces()->pluck('workspaces.id');
        $plansFromWorkspaces = Plan::whereIn('workspace_id', $workspaceIds)->with('boards')->get();
        $individuallySharedPlans = $user->sharedPlans()->with('boards')->get();

        return response()->json(
            $plansFromWorkspaces->concat($individuallySharedPlans)->unique('id')->values()
        );
    }
}
