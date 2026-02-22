<?php

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id'  => ['sometimes', 'integer', 'exists:clients,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'status'     => ['sometimes', Rule::enum(InvoiceStatus::class)],
            'amount'     => ['sometimes', 'numeric', 'min:0'],
            'tax'        => ['nullable', 'numeric', 'min:0'],
            'total'      => ['sometimes', 'numeric', 'min:0'],
            'notes'      => ['nullable', 'string'],
            'due_date'   => ['nullable', 'date'],
        ];
    }
}
