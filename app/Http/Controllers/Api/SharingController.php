<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Models\Plan;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Facades\Auth;

class SharingController extends Controller
{
    public function invite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:owner,editor,viewer',
            'type' => 'required|string|in:Workspace,Plan',
            'id' => 'required|integer'
        ]);

        $type = $validated['type'] === 'Workspace' ? Workspace::class : Plan::class;
        $entity = $type::findOrFail($validated['id']);

        // Check permissions (only owners can invite)
        $this->authorizeOwner($entity);

        // Check if user already exists
        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            if ($validated['type'] === 'Workspace') {
                $entity->members()->syncWithoutDetaching([
                    $user->id => ['role' => $validated['role']]
                ]);
            } else {
                $entity->members()->syncWithoutDetaching([
                    $user->id => ['role' => $validated['role']]
                ]);
            }
            return response()->json(['message' => 'User added successfully.']);
        }

        // Otherwise create invitation
        $token = \Illuminate\Support\Str::random(32);
        
        $invitation = Invitation::updateOrCreate(
            [
                'email' => $validated['email'],
                'inviteable_type' => $type,
                'inviteable_id' => $validated['id'],
            ],
            [
                'role' => $validated['role'],
                'invited_by' => Auth::id(),
                'status' => 'pending',
                'token' => $token,
                'expires_at' => now()->addDays(7)
            ]
        );

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($validated['email'])->send(new \App\Mail\WorkspaceInvitation($invitation));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send invitation email: " . $e->getMessage());
        }

        return response()->json(['message' => 'Invitation created successfully. Note: Email notification may be delayed.']);
    }

    public function acceptInvitation(Request $request) 
    {
        $validated = $request->validate(['token' => 'required|string']);
        
        $invitation = Invitation::where('token', $validated['token'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // If user is authenticated
        if (Auth::check()) {
             $user = Auth::user();
             // Verify email matches (optional safety, or allow accepting if logged in with diff email?)
             // Requirement says: "When the invited user signs in... using the same email".
             // So we should strictly enforce email match if we want to follow that?
             // Or if they click the link, they might want to accept with their current logged in user.
             // But usually security implies email match.
             
             if ($user->email !== $invitation->email) {
                 return response()->json(['message' => 'This invitation was sent to a different email address.'], 403);
             }
             
             // Accept logic
             $invitation->inviteable->members()->syncWithoutDetaching([
                 $user->id => ['role' => $invitation->role]
             ]);
             
             $invitation->update(['status' => 'accepted']);
             
             return response()->json(['message' => 'Invitation accepted', 'workspace' => $invitation->inviteable]);
        }
        
        return response()->json(['message' => 'Please login to accept invitation'], 401);
    }

    public function getMembers($type, $id)
    {
        $model = $type === 'workspace' ? Workspace::class : Plan::class;
        $entity = $model::with('members')->findOrFail($id);
        
        return response()->json($entity->members);
    }

    public function removeMember(Request $request, $type, $id, $userId)
    {
        $model = $type === 'workspace' ? Workspace::class : Plan::class;
        $entity = $model::findOrFail($id);

        $this->authorizeOwner($entity);

        if ($userId == Auth::id() && $entity->owner_id == $userId) {
            return response()->json(['message' => 'Owner cannot be removed.'], 422);
        }

        $entity->members()->detach($userId);

        return response()->json(['message' => 'Member removed.']);
    }

    protected function authorizeOwner($entity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Literal owner
        if (isset($entity->owner_id) && $entity->owner_id == $user->id) return;
        if (isset($entity->user_id) && $entity->user_id == $user->id) return;

        // 2. Member with owner role
        $isOwner = $entity->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            abort(403, 'Only owners can manage sharing.');
        }
    }
}
