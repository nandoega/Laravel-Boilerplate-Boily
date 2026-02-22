<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'project_id'      => $this->project_id,
            'title'           => $this->title,
            'description'     => $this->description,
            'status'          => $this->status,
            'priority'        => $this->priority,
            'due_date'        => $this->due_date?->format('Y-m-d'),
            'estimated_hours' => $this->estimated_hours,
            'assignee'        => $this->whenLoaded('assignee'),
            'project'         => $this->whenLoaded('project'),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
