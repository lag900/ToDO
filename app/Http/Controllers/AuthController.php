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
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events',
                'email',
                'profile',
                'openid'
            ])
            ->with([
                'access_type' => 'offline', 
                'prompt' => 'consent select_account'
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google authentication failed.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();
        
        $expiresAt = isset($googleUser->expiresIn) ? now()->addSeconds($googleUser->expiresIn) : null;

        if ($user) {
            $updateData = [
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'google_token' => $googleUser->token,
                'google_token_expires_at' => $expiresAt,
            ];

            // Only update refresh token if Google actually sends it
            // (Shared hosting: don't overwrite a good old token with null)
            if ($googleUser->refreshToken) {
                $updateData['google_refresh_token'] = $googleUser->refreshToken;
            }

            // Success: Reset error and set granted flag
            $updateData['google_calendar_error'] = null;
            $updateData['google_calendar_scopes_granted'] = true;

            $user->update($updateData);
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'display_name' => $googleUser->getName(),
                'password' => null,
                'has_completed_onboarding' => false,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'google_token_expires_at' => $expiresAt,
                'google_calendar_scopes_granted' => true,
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
            'notification_settings.exclude_tasks_created_by_me' => 'boolean',
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
