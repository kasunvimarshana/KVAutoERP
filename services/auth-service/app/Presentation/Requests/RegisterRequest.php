<?php

declare(strict_types=1);

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Registration HTTP Request - Handles all validation for user registration.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'tenant_id' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:user,manager,admin'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
