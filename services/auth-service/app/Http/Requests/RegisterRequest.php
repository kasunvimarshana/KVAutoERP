<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string', 'max:255'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => [
                'required', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')
                    ->where('tenant_id', $this->input('tenant_id')),
            ],
            'password'  => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role'      => ['nullable', 'string', 'in:viewer,staff'],
        ];
    }
}
