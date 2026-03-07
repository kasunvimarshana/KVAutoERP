<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'username'   => ['sometimes', 'required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'email'      => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'required', 'string', 'max:100'],
            'phone'      => ['sometimes', 'nullable', 'string', 'max:20'],
            'roles'      => ['sometimes', 'array'],
            'roles.*'    => ['string', 'in:admin,manager,warehouse-manager,viewer,customer'],
            'attributes' => ['sometimes', 'array'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }
}
