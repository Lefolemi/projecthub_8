<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public routes (no token required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires valid Sanctum Bearer token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Projects & Team Invitations
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::post('/projects/{project}/invite', [ProjectController::class, 'invite']);

    Route::get('/invitations', [InvitationController::class, 'index']);
    Route::post('/invitations/{invitation}/accept', [InvitationController::class, 'accept']);
    Route::post('/invitations/{invitation}/decline', [InvitationController::class, 'decline']);

    // Member & Admin task routes (view, create, update)
    Route::apiResource('tasks', TaskController::class)->except(['destroy']);

    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/summary', [AdminController::class, 'summary']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    });
});