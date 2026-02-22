<?php

namespace App\Http\Resources\TimeEntry;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'task_id'         => $this->task_id,
            'user_id'         => $this->user_id,
            'description'     => $this->description,
            'hours'           => (float) $this->hours,
            'is_billable'     => $this->is_billable,
            'hourly_rate'     => (float) $this->hourly_rate,
            'billable_amount' => $this->billable_amount, // from the accessor
            'date'            => $this->date?->format('Y-m-d'),
            'task'            => $this->whenLoaded('task'),
            'user'            => $this->whenLoaded('user'),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
