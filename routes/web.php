<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AuthController;

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// --- DATABASE MAINTENANCE & FIX TOOLS ---
Route::get('/maintain/migrate', function () {
    if (request()->query('key') !== 'admin123') return "Unauthorized. Access key required.";
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return "<h1>✅ Migrations Executed!</h1><pre>$output</pre>";
    } catch (\Exception $e) {
        return "<h1>❌ Migration Error</h1><p>" . $e->getMessage() . "</p>";
    }
});

Route::get('/maintain/clear-cache', function () {
    if (request()->query('key') !== 'admin123') return "Unauthorized.";
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    return "<h1>⚡ Cache Cleared!</h1>";
});

Route::get('/delivery-file/{filename}', [\App\Http\Controllers\Api\TaskDeliveryController::class, 'showFile']);

// --- PUBLIC PAGES (Blade) for Google OAuth Compliance ---
Route::get('/', function () {
    if (Auth::check()) {
        return view('app');
    }
    return view('public.landing');
});
Route::view('/privacy-policy', 'public.privacy');
Route::view('/terms-of-service', 'public.terms');

// --- SPA MAIN ROUTE ---
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|auth|maintain|delivery-file|google).*$');
