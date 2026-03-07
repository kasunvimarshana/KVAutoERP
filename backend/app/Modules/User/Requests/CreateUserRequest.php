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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'tenant_id' => 'required|integer|exists:tenants,id',
            'role' => 'sometimes|string|exists:roles,name',
            'attributes' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
