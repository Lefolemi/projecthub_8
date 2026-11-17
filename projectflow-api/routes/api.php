<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public routes (no token required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires valid Sanctum Bearer token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Member & Admin task routes (view, create, update)
    Route::apiResource('tasks', TaskController::class)->except(['destroy']);

    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/summary', [AdminController::class, 'summary']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    });
});