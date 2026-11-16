<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:todo,in_progress,done'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'estimated_hours' => ['sometimes', 'required', 'numeric', 'min:0.5'],
        ];
    }
}