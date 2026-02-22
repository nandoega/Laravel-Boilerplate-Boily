<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'invoice_id'    => $this->invoice_id,
            'amount'        => (float) $this->amount,
            'method'        => $this->method,
            'reference'     => $this->reference,
            'notes'         => $this->notes,
            'paid_at'       => $this->paid_at?->toISOString(),
            'is_refunded'   => $this->is_refunded,
            'refunded_at'   => $this->refunded_at?->toISOString(),
            'refund_reason' => $this->refund_reason,
            'invoice'       => $this->whenLoaded('invoice'),
            'created_at'    => $this->created_at?->toISOString(),
        ];
    }
}
