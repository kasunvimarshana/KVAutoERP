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
            'username'    => ['required', 'string', 'max:100', 'unique:users,username'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:20'],
            'password'    => ['required', 'string', 'min:8'],
            'roles'       => ['sometimes', 'array'],
            'roles.*'     => ['string', 'in:admin,manager,warehouse-manager,viewer,customer'],
            'attributes'  => ['sometimes', 'array'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
