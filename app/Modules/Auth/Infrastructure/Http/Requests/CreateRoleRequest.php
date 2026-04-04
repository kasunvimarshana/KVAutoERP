<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'   => ['required', 'integer'],
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_system'   => ['sometimes', 'boolean'],
        ];
    }
}
