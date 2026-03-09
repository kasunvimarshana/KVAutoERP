<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Login Request.
 *
 * Validates login credentials. Authorization check happens in AuthService.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email'               => ['required', 'email', 'max:255'],
            'password'            => ['required', 'string', 'min:8'],
            'device'              => ['sometimes', 'array'],
            'device.device_name'  => ['sometimes', 'string', 'max:255'],
            'device.device_type'  => ['sometimes', 'string', 'max:50'],
            'device.device_token' => ['sometimes', 'string'],
        ];
    }
}
