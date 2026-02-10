<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use Illuminate\Http\Request;
// ...existing imports...

class GoogleController extends Controller
{
    // ...existing methods...

    public function redirectToGoogle()
    {
        // Request only the allowed scopes
        return Socialite::driver('google')
            ->scopes(config('services.google.scopes'))
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // ...existing user lookup/creation logic...

        $refreshToken = $googleUser->refreshToken ?? null;
        if ($refreshToken) {
            // store encrypted refresh token; do NOT log it
            $user->forceFill([
                'google_refresh_token' => encrypt($refreshToken),
            ])->save();
        }

        // ...existing login/redirect code...
    }

    // ...existing methods...
}
