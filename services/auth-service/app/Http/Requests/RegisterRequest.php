<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Shared\Base\BaseRequest;

/**
 * Register Form Request.
 *
 * Validates the payload for POST /auth/register.
 */
final class RegisterRequest extends BaseRequest
{
    /**
     * @return array<string, array<string>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->tenantId() ?? $this->header('X-Tenant-ID', '');

        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => [
                'required',
                'string',
                'email',
                'max:255',
                // Unique per tenant, not globally.
                \Illuminate\Validation\Rule::unique('users', 'email')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('deleted_at'),
            ],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'              => 'A display name is required.',
            'email.required'             => 'An e-mail address is required.',
            'email.email'                => 'Please provide a valid e-mail address.',
            'email.unique'               => 'That e-mail address is already registered in this organisation.',
            'password.required'          => 'A password is required.',
            'password.min'               => 'The password must be at least 8 characters.',
            'password.confirmed'         => 'The password confirmation does not match.',
            'password_confirmation.required' => 'Please confirm the password.',
        ];
    }
}
