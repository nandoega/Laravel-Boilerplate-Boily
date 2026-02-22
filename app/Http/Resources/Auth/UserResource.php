<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'avatar'     => $this->avatar,
            'is_active'  => $this->is_active,
            'roles'      => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions'=> $this->whenLoaded('permissions', fn () => $this->getAllPermissions()->pluck('name')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
