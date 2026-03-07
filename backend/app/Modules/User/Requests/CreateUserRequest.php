<?php

namespace App\Modules\User\Requests;

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
            'username'    => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email'       => ['required', 'email', 'max:255'],
            'password'    => ['required', 'string', 'min:8'],
            'first_name'  => ['nullable', 'string', 'max:100'],
            'last_name'   => ['nullable', 'string', 'max:100'],
            'role'        => ['nullable', 'string', 'in:admin,manager,staff,viewer'],
            'is_active'   => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
        ];
    }
}
