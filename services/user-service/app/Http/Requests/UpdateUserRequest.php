<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['sometimes', 'string', 'max:255'],
            'email'           => ['sometimes', 'email', 'max:255'],
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
}
