<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Shared\Base\BaseRequest;

/**
 * Login Form Request.
 *
 * Validates the payload for POST /auth/login.
 */
final class LoginRequest extends BaseRequest
{
    /**
     * @return array<string, array<string>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'An e-mail address is required.',
            'email.email'       => 'Please provide a valid e-mail address.',
            'password.required' => 'A password is required.',
            'password.min'      => 'The password must be at least 8 characters.',
        ];
    }
}
