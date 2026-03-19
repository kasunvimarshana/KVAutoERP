<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates user registration requests (admin-only endpoint).
 */
final class RegisterRequest extends FormRequest
{
    /**
     * Authorization is enforced in the controller by checking the
     * admin role in the JWT claims.
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
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email:rfc,dns', 'max:255'],
            'password'        => ['required', 'string', 'min:12', 'confirmed'],
            'roles'           => ['sometimes', 'array'],
            'roles.*'         => ['string', 'max:100'],
            'permissions'     => ['sometimes', 'array'],
            'permissions.*'   => ['string', 'max:100'],
            'organization_id' => ['sometimes', 'nullable', 'uuid'],
            'branch_id'       => ['sometimes', 'nullable', 'uuid'],
            'is_active'       => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.email'        => 'Please provide a valid email address.',
            'password.min'       => 'Password must be at least 12 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
