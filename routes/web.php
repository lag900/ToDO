<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);


// --- DATABASE TOOLS (اختياري) ---
Route::get('/maintain/migrate', function () {
    if (request()->query('key') !== 'admin123') return "Unauthorized.";
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "<h1>✅ Migrations Executed!</h1>";
});

Route::get('/maintain/clear-cache', function () {
    if (request()->query('key') !== 'admin123') return "Unauthorized.";
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "<h1>⚡ Cache Cleared!</h1>";
});


// ===============================
// 🔥 PUBLIC PAGES (IMPORTANT FOR GOOGLE)
// ===============================

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('public.landing');
});

Route::view('/privacy-policy', 'public.privacy');
Route::view('/terms-of-service', 'public.terms');


// ===============================
// 🔥 SPA MAIN ROUTE (لا تلمس)
// ===============================
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|auth|maintain|delivery-file).*$');
