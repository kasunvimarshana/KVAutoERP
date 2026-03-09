<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create Tenant Request.
 */
class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['sometimes', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', Rule::unique('tenants', 'slug')],
            'domain'        => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')],
            'plan'          => ['sometimes', 'string', 'in:starter,professional,enterprise'],
            'status'        => ['sometimes', 'string', 'in:active,inactive'],
            'configuration' => ['sometimes', 'array'],
            'metadata'      => ['sometimes', 'array'],
        ];
    }
}
