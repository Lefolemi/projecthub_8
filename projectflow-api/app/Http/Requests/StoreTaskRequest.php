<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,in_progress,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'estimated_hours' => ['required', 'numeric', 'min:0.5'],
        ];
    }

    public function messages()
    {
        return [
            'project_id.exists' => 'Selected project does not exist.',
            'title.required' => 'Task title is mandatory and cannot be empty.',
            'status.in' => 'Status must be one of: todo, in_progress, or done.',
            'priority.in' => 'Priority must be low, medium, or high.',
            'estimated_hours.min' => 'Estimated hours must be at least 0.5 hours.',
        ];
    }
}