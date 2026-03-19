<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255'],
            'password'        => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'avatar'          => ['nullable', 'url', 'max:2048'],
            'organisation_id' => ['nullable', 'uuid'],
            'branch_id'       => ['nullable', 'uuid'],
            'location_id'     => ['nullable', 'uuid'],
            'department_id'   => ['nullable', 'uuid'],
            'is_active'       => ['boolean'],
            'metadata'        => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Full name is required.',
            'email.required'    => 'Email address is required.',
            'email.email'       => 'A valid email address is required.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
