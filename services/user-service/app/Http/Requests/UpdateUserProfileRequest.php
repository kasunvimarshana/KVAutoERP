<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the request body for updating a user profile.
 * All fields are nullable/optional on update.
 */
final class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'           => ['nullable', 'email', 'max:255'],
            'first_name'      => ['nullable', 'string', 'max:100'],
            'last_name'       => ['nullable', 'string', 'max:100'],
            'display_name'    => ['nullable', 'string', 'max:150'],
            'organization_id' => ['nullable', 'uuid'],
            'branch_id'       => ['nullable', 'uuid'],
            'location_id'     => ['nullable', 'uuid'],
            'department_id'   => ['nullable', 'uuid'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'locale'          => ['nullable', 'string', 'max:10'],
            'timezone'        => ['nullable', 'string', 'max:50'],
            'metadata'        => ['nullable', 'array'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }
}
