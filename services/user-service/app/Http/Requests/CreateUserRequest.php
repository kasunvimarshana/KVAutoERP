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
            'email'           => ['required', 'email', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8'],
            'tenant_id'       => ['required', 'uuid'],
            'organization_id' => ['sometimes', 'uuid'],
            'branch_id'       => ['sometimes', 'uuid'],
            'role_ids'        => ['sometimes', 'array'],
            'role_ids.*'      => ['uuid'],
        ];
    }
}
