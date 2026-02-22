<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'budget'      => $this->budget,
            'start_date'  => $this->start_date?->format('Y-m-d'),
            'end_date'    => $this->end_date?->format('Y-m-d'),
            'client'      => $this->whenLoaded('client'),
            'owner'       => $this->whenLoaded('owner'),
            'members'     => $this->whenLoaded('members'),
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
