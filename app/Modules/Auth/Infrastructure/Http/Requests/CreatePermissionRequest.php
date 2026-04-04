<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:permissions,slug'],
            'description' => ['sometimes', 'nullable', 'string'],
            'module'      => ['required', 'string', 'max:100'],
            'action'      => ['required', 'string', 'max:100'],
        ];
    }
}
