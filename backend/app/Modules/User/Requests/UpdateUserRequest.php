<?php

namespace App\Modules\User\Requests;

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
            'email'       => ['sometimes', 'email', 'max:255'],
            'first_name'  => ['sometimes', 'nullable', 'string', 'max:100'],
            'last_name'   => ['sometimes', 'nullable', 'string', 'max:100'],
            'role'        => ['sometimes', 'string', 'in:admin,manager,staff,viewer'],
            'is_active'   => ['sometimes', 'boolean'],
            'permissions' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
