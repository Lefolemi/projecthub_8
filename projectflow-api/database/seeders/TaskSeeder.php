<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $tasks = [
            ['title' => 'Design Database', 'description' => 'Create ERD and migrations', 'status' => 'done', 'priority' => 'high', 'due_date' => '2026-11-10', 'estimated_hours' => 4.0],
            ['title' => 'Build REST API', 'description' => 'Implement Resource Controller & Form Requests', 'status' => 'in_progress', 'priority' => 'high', 'due_date' => '2026-11-15', 'estimated_hours' => 8.0],
            ['title' => 'Build Dashboard', 'description' => 'Connect Vue frontend to Laravel API', 'status' => 'todo', 'priority' => 'medium', 'due_date' => '2026-11-18', 'estimated_hours' => 6.5],
            ['title' => 'Integration Testing', 'description' => 'Test all endpoints and edge cases', 'status' => 'todo', 'priority' => 'medium', 'due_date' => '2026-11-20', 'estimated_hours' => 5.0],
            ['title' => 'Deployment', 'description' => 'Deploy to production server', 'status' => 'todo', 'priority' => 'low', 'due_date' => '2026-11-25', 'estimated_hours' => 3.0],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}