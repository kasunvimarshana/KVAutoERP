<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'    => ['required', 'uuid'],
            'service_name' => ['required', 'string', 'max:100'],
            'config_key'   => ['required', 'string', 'max:200', 'regex:/^[a-z0-9._-]+$/'],
            'config_value' => ['required', 'array'],
            'config_type'  => ['required', 'string', 'in:string,json,boolean,integer'],
            'is_encrypted' => ['boolean'],
            'is_active'    => ['boolean'],
            'description'  => ['nullable', 'string', 'max:500'],
            'metadata'     => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required'    => 'Tenant identifier is required.',
            'tenant_id.uuid'        => 'Tenant identifier must be a valid UUID.',
            'service_name.required' => 'Service name is required.',
            'config_key.required'   => 'Configuration key is required.',
            'config_key.regex'      => 'Configuration key must use lowercase letters, numbers, dots, underscores, or hyphens.',
            'config_value.required' => 'Configuration value is required.',
            'config_type.in'        => 'Configuration type must be one of: string, json, boolean, integer.',
        ];
    }
}
