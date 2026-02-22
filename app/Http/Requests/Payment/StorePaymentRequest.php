<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'method'     => ['nullable', 'string', 'max:50'],
            'reference'  => ['nullable', 'string', 'max:255'],
            'notes'      => ['nullable', 'string'],
            'paid_at'    => ['nullable', 'date'],
        ];
    }
}
