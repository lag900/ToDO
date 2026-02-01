<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/complete-onboarding', [AuthController::class, 'completeOnboarding']);
    Route::patch('/user/settings', [AuthController::class, 'updateSettings']);
    
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{task}/checklists', [TaskController::class, 'addChecklist']);
    Route::post('/checklists/{item}/toggle', [TaskController::class, 'toggleChecklist']);

    Route::get('/workspaces', [\App\Http\Controllers\Api\WorkspaceController::class, 'index']);
    Route::post('/workspaces', [\App\Http\Controllers\Api\WorkspaceController::class, 'store']);
    Route::delete('/workspaces/{id}', [\App\Http\Controllers\Api\WorkspaceController::class, 'destroy']);

    Route::get('/plans', [\App\Http\Controllers\Api\PlanController::class, 'index']);
    
    // Sharing
    Route::post('/share/invite', [\App\Http\Controllers\Api\SharingController::class, 'invite']);
    Route::post('/share/accept', [\App\Http\Controllers\Api\SharingController::class, 'acceptInvitation']);
    Route::get('/share/members/{type}/{id}', [\App\Http\Controllers\Api\SharingController::class, 'getMembers']);
    Route::delete('/share/members/{type}/{id}/{userId}', [\App\Http\Controllers\Api\SharingController::class, 'removeMember']);



    // Task Delivery
    Route::post('/tasks/{task}/deliver', [\App\Http\Controllers\Api\TaskDeliveryController::class, 'store']);
    Route::post('/delivery-upload', [\App\Http\Controllers\Api\TaskDeliveryController::class, 'upload']);
    Route::delete('/deliveries/{delivery}', [\App\Http\Controllers\Api\TaskDeliveryController::class, 'destroy']);
});
