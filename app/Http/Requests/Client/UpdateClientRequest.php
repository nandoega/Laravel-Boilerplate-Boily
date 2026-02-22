<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $clientId = $this->route('client'); // assumes parameter is 'client' or the ID directly

        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255', 'unique:clients,email,' . $clientId],
            'phone'     => ['nullable', 'string', 'max:50'],
            'company'   => ['nullable', 'string', 'max:255'],
            'address'   => ['nullable', 'string'],
            'notes'     => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
