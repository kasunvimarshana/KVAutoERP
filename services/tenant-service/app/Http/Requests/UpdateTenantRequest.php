<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->route('id') ?? $this->route('tenant');

        return [
            'name'   => ['sometimes', 'required', 'string', 'max:255'],
            'slug'   => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', Rule::unique('tenants', 'slug')->ignore($tenantId)],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->ignore($tenantId)],
            'email'  => ['sometimes', 'required', 'email', 'max:255'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'in:active,inactive,suspended,trial'],
            'plan'   => ['nullable', 'string', 'in:free,starter,professional,enterprise'],
            'settings'             => ['nullable', 'array'],
            'db_config'            => ['nullable', 'array'],
            'cache_config'         => ['nullable', 'array'],
            'mail_config'          => ['nullable', 'array'],
            'broker_config'        => ['nullable', 'array'],
            'metadata'             => ['nullable', 'array'],
            'trial_ends_at'        => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date'],
        ];
    }
}
