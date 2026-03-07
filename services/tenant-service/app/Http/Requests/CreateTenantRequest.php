<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'slug'   => ['required', 'string', 'max:100', 'alpha_dash', 'unique:tenants,slug'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'email'  => ['required', 'email', 'max:255'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'plan'   => ['nullable', 'string', 'in:free,starter,professional,enterprise'],
            'settings'      => ['nullable', 'array'],
            'db_config'     => ['nullable', 'array'],
            'cache_config'  => ['nullable', 'array'],
            'mail_config'   => ['nullable', 'array'],
            'broker_config' => ['nullable', 'array'],
            'metadata'      => ['nullable', 'array'],
            'trial_ends_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Tenant name is required.',
            'slug.required'  => 'A unique slug is required for the tenant.',
            'slug.unique'    => 'This slug is already in use.',
            'email.required' => 'A contact email is required.',
            'email.email'    => 'The email address format is invalid.',
            'plan.in'        => 'Plan must be one of: free, starter, professional, enterprise.',
        ];
    }
}
