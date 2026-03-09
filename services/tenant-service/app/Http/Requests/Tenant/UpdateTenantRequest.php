<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Domain\Tenant\Entities\Tenant;
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
        $tenantId = $this->route('id');

        return [
            'name'                 => ['sometimes', 'string', 'max:255'],
            'slug'                 => ['sometimes', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', Rule::unique('tenants', 'slug')->ignore($tenantId)],
            'domain'               => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->ignore($tenantId)],
            'plan'                 => ['sometimes', 'string', 'in:' . implode(',', [
                Tenant::PLAN_FREE,
                Tenant::PLAN_STARTER,
                Tenant::PLAN_PROFESSIONAL,
                Tenant::PLAN_ENTERPRISE,
            ])],
            'status'               => ['sometimes', 'string', 'in:' . implode(',', [
                Tenant::STATUS_ACTIVE,
                Tenant::STATUS_INACTIVE,
                Tenant::STATUS_SUSPENDED,
                Tenant::STATUS_PENDING,
            ])],
            'max_users'            => ['sometimes', 'integer', 'min:0'],
            'max_organizations'    => ['sometimes', 'integer', 'min:0'],
            'trial_ends_at'        => ['sometimes', 'nullable', 'date'],
            'subscription_ends_at' => ['sometimes', 'nullable', 'date'],
            'settings'             => ['sometimes', 'array'],
            'config'               => ['sometimes', 'array'],
            'metadata'             => ['sometimes', 'array'],
        ];
    }
}
