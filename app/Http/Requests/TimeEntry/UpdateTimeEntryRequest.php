<?php

namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeEntryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'task_id'     => ['sometimes', 'integer', 'exists:tasks,id'],
            'user_id'     => ['sometimes', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'hours'       => ['sometimes', 'numeric', 'min:0.1', 'max:24'],
            'is_billable' => ['sometimes', 'boolean'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'date'        => ['sometimes', 'date'],
        ];
    }
}
