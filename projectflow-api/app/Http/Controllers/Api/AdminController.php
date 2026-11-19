<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;

class AdminController extends Controller
{
    public function summary()
    {
        return response()->json([
            'message' => 'Admin access granted',
            'summary' => [
                'total_users' => User::count(),
                'total_tasks' => Task::count(),
                'tasks_by_status' => [
                    'todo' => Task::where('status', 'todo')->count(),
                    'in_progress' => Task::where('status', 'in_progress')->count(),
                    'done' => Task::where('status', 'done')->count(),
                ],
            ],
            'users' => User::select('id', 'name', 'email', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }
}