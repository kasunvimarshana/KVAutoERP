<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFeatureFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'          => ['required', 'uuid'],
            'flag_key'           => ['required', 'string', 'max:200', 'regex:/^[a-z0-9._-]+$/'],
            'is_enabled'         => ['boolean'],
            'rollout_percentage' => ['integer', 'min:0', 'max:100'],
            'conditions'         => ['nullable', 'array'],
            'description'        => ['nullable', 'string', 'max:500'],
            'metadata'           => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required'         => 'Tenant identifier is required.',
            'tenant_id.uuid'             => 'Tenant identifier must be a valid UUID.',
            'flag_key.required'          => 'Feature flag key is required.',
            'flag_key.regex'             => 'Flag key must use lowercase letters, numbers, dots, underscores, or hyphens.',
            'rollout_percentage.min'     => 'Rollout percentage must be between 0 and 100.',
            'rollout_percentage.max'     => 'Rollout percentage must be between 0 and 100.',
        ];
    }
}
