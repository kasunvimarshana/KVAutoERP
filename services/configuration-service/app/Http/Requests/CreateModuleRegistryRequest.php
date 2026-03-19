<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateModuleRegistryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'     => ['required', 'uuid'],
            'module_name'   => ['required', 'string', 'max:200'],
            'module_key'    => ['required', 'string', 'max:200', 'regex:/^[a-z0-9._-]+$/'],
            'is_enabled'    => ['boolean'],
            'configuration' => ['nullable', 'array'],
            'dependencies'  => ['nullable', 'array'],
            'dependencies.*' => ['string', 'max:200'],
            'version'       => ['nullable', 'string', 'max:50'],
            'metadata'      => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required'   => 'Tenant identifier is required.',
            'tenant_id.uuid'       => 'Tenant identifier must be a valid UUID.',
            'module_name.required' => 'Module name is required.',
            'module_key.required'  => 'Module key is required.',
            'module_key.regex'     => 'Module key must use lowercase letters, numbers, dots, underscores, or hyphens.',
        ];
    }
}
