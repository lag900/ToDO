<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// --- DATABASE FIX ROUTE ---
Route::get('/fix-database-issue', function () {
    try {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE tasks MODIFY COLUMN project_id BIGINT UNSIGNED NULL");
        return "<h1>✅ Database Fixed!</h1><p>The 'project_id' column is now nullable. You can try creating a task now.</p>";
    } catch (\Exception $e) {
        // If the error is about syntax (sqlite vs mysql), try generalized schema builder
        try {
             \Illuminate\Support\Facades\Schema::table('tasks', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->unsignedBigInteger('project_id')->nullable()->change();
            });
            return "<h1>✅ Database Fixed (via Schema)!</h1><p>The 'project_id' column is now nullable.</p>";
        } catch (\Exception $e2) {
             return "<h1>❌ Error</h1><p>" . $e->getMessage() . "</p><p>" . $e2->getMessage() . "</p>";
        }
    }
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
