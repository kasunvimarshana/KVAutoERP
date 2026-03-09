<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $tenantId = $this->route('id') ?? $this->route('tenant');

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'slug'          => ['sometimes', 'string', 'max:100', "unique:tenants,slug,{$tenantId}"],
            'domain'        => ['sometimes', 'string', 'max:255', "unique:tenants,domain,{$tenantId}"],
            'plan'          => ['sometimes', 'in:free,starter,pro,enterprise'],
            'is_active'     => ['sometimes', 'boolean'],
            'settings'      => ['nullable', 'array'],
            'database_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
