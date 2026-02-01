<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google authentication failed.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'display_name' => $googleUser->getName(),
                'password' => null,
                'has_completed_onboarding' => false,
            ]);
        }

        $this->processPendingInvitations($user);
        Auth::login($user);

        if (!$user->has_completed_onboarding) {
            return redirect('/welcome');
        }

        return redirect('/');
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->workspaces_count = $user->workspaces()->count();
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out']);
    }

    public function completeOnboarding(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->update([
            'name' => $request->display_name,
            'display_name' => $request->display_name,
            'has_completed_onboarding' => true,
        ]);

        return response()->json($user);
    }
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'notification_settings' => 'required|array',
            'notification_settings.email_enabled' => 'boolean',
            'notification_settings.types' => 'array',
            'notification_settings.exclude_self' => 'boolean',
        ]);

        $user = $request->user();
        $user->update([
            'notification_settings' => array_merge($user->notification_settings ?? [], $validated['notification_settings'])
        ]);

        return response()->json($user);
    }

    protected function processPendingInvitations(User $user)
    {
        $invitations = \App\Models\Invitation::where('email', $user->email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();

        /** @var \App\Models\Invitation $invitation */
        foreach ($invitations as $invitation) {
            if ($invitation->inviteable_type === 'App\Models\Workspace') {
                $user->workspaces()->syncWithoutDetaching([
                    $invitation->inviteable_id => ['role' => $invitation->role]
                ]);
            } elseif ($invitation->inviteable_type === 'App\Models\Plan') {
                $user->sharedPlans()->syncWithoutDetaching([
                    $invitation->inviteable_id => ['role' => $invitation->role]
                ]);
            }
            $invitation->update(['status' => 'accepted']);
        }
    }
}
