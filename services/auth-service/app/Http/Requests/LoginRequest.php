<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates login credentials.
 */
final class LoginRequest extends FormRequest
{
    /**
     * All auth endpoints are publicly accessible; authorisation
     * is performed by the auth service itself.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'     => ['required', 'email:rfc'],
            'password'  => ['required', 'string', 'min:8'],
            'tenant_id' => ['required', 'uuid'],
            'device_id' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'     => 'An email address is required.',
            'email.email'        => 'Please provide a valid email address.',
            'password.required'  => 'A password is required.',
            'password.min'       => 'Password must be at least 8 characters.',
            'tenant_id.required' => 'A tenant identifier is required.',
            'tenant_id.uuid'     => 'Tenant identifier must be a valid UUID.',
            'device_id.required' => 'A device identifier is required.',
        ];
    }
}
