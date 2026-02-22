<?php

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id'  => ['required', 'integer', 'exists:clients,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'status'     => ['nullable', Rule::enum(InvoiceStatus::class)],
            'amount'     => ['required', 'numeric', 'min:0'],
            'tax'        => ['nullable', 'numeric', 'min:0'],
            'total'      => ['required', 'numeric', 'min:0'],
            'notes'      => ['nullable', 'string'],
            'due_date'   => ['nullable', 'date'],
        ];
    }
}
