<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'estimated_hours' => (float) $this->estimated_hours,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}