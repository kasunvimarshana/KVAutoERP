<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Requests;

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
            'tenant_id' => ['sometimes', 'integer'],
            'org_unit_id' => ['sometimes', 'nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'timezone' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
