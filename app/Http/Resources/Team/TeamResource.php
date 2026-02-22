<?php

namespace App\Http\Resources\Team;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'owner'       => $this->whenLoaded('owner'),
            'members'     => $this->whenLoaded('members'),
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
