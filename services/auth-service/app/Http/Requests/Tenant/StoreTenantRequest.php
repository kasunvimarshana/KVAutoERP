<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'slug'   => ['required', 'string', 'max:100', 'alpha_dash', 'unique:tenants,slug'],
            'domain' => ['nullable', 'string', 'max:255'],
            'plan'   => ['nullable', 'string', 'in:free,starter,pro,enterprise'],
            'config' => ['nullable', 'array'],
        ];
    }
}
