<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'status'         => $this->status,
            'amount'         => (float) $this->amount,
            'tax'            => (float) $this->tax,
            'total'          => (float) $this->total,
            'notes'          => $this->notes,
            'due_date'       => $this->due_date?->format('Y-m-d'),
            'paid_at'        => $this->paid_at?->toISOString(),
            'client'         => $this->whenLoaded('client'),
            'project'        => $this->whenLoaded('project'),
            'payments'       => $this->whenLoaded('payments'),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }
}
