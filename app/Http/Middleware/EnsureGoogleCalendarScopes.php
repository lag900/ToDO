<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureGoogleCalendarScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // If user has integration but needs reconnect, redirect to Google
            // Only redirect on GET requests and not for the auth routes themselves
            if ($user->google_calendar_needs_reconnect && 
                $request->isMethod('GET') && 
                !$request->is('auth/*') && 
                !$request->is('logout') &&
                !$request->expectsJson()) {
                
                return redirect('/auth/google');
            }
        }

        return $next($request);
    }
}
