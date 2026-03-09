<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'min:2', 'max:255'],
            'email'           => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password'        => [
                'required',
                'string',
                'min:8',
                'max:72',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
            ],
            'organization_id' => ['nullable', 'uuid'],
            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['string', 'in:user,viewer,manager,admin'],
            'metadata'        => ['nullable', 'array'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ];
    }
}
