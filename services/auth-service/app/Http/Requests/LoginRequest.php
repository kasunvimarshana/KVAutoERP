<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'           => ['required', 'email', 'max:255'],
            'password'        => ['required', 'string', 'min:8', 'max:255'],
            'tenant_id'       => ['required', 'uuid'],
            'device_id'       => ['required', 'string', 'max:255'],
            'device_name'     => ['nullable', 'string', 'max:100'],
            'organisation_id' => ['nullable', 'uuid'],
            'branch_id'       => ['nullable', 'uuid'],
            'remember_me'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'     => 'Email address is required.',
            'password.required'  => 'Password is required.',
            'tenant_id.required' => 'Tenant identifier is required.',
            'tenant_id.uuid'     => 'Tenant identifier must be a valid UUID.',
            'device_id.required' => 'Device identifier is required.',
        ];
    }
}
