<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Tenant Request.
 */
class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'   => ['sometimes', 'string', 'max:255'],
            'domain' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->ignore($this->route('id'))],
            'plan'   => ['sometimes', 'string', 'in:starter,professional,enterprise'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
