<?php

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
            'email'     => ['required', 'email', 'max:255'],
            'password'  => ['required', 'string', 'min:8'],
            'tenant_id' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'     => 'Email address is required.',
            'password.required'  => 'Password is required.',
            'tenant_id.required' => 'Tenant identifier is required.',
        ];
    }
}
