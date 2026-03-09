<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:100', 'unique:tenants,slug'],
            'domain'        => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'plan'          => ['sometimes', 'in:free,starter,pro,enterprise'],
            'is_active'     => ['sometimes', 'boolean'],
            'settings'      => ['nullable', 'array'],
            'database_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
