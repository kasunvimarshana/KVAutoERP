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
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            'avatar'    => ['sometimes', 'nullable', 'string'],
            'timezone'  => ['sometimes', 'string', 'timezone:all'],
            'locale'    => ['sometimes', 'string', 'max:10'],
            'status'    => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}
