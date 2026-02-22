<?php

namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'task_id'     => ['required', 'integer', 'exists:tasks,id'],
            'user_id'     => ['required', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'hours'       => ['required', 'numeric', 'min:0.1', 'max:24'],
            'is_billable' => ['boolean'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'date'        => ['required', 'date'],
        ];
    }
}
