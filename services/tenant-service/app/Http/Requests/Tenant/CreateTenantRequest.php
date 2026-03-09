<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Domain\Tenant\Entities\Tenant;
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
            'name'                 => ['required', 'string', 'max:255'],
            'slug'                 => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', 'unique:tenants,slug'],
            'domain'               => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'plan'                 => ['required', 'string', 'in:' . implode(',', [
                Tenant::PLAN_FREE,
                Tenant::PLAN_STARTER,
                Tenant::PLAN_PROFESSIONAL,
                Tenant::PLAN_ENTERPRISE,
            ])],
            'status'               => ['nullable', 'string', 'in:' . implode(',', [
                Tenant::STATUS_ACTIVE,
                Tenant::STATUS_INACTIVE,
                Tenant::STATUS_SUSPENDED,
                Tenant::STATUS_PENDING,
            ])],
            'max_users'            => ['nullable', 'integer', 'min:0'],
            'max_organizations'    => ['nullable', 'integer', 'min:0'],
            'trial_ends_at'        => ['nullable', 'date'],
            'settings'             => ['nullable', 'array'],
            'config'               => ['nullable', 'array'],
            'database_config'      => ['nullable', 'array'],
            'database_config.host' => ['nullable', 'string'],
            'database_config.port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'database_config.database' => ['nullable', 'string'],
            'database_config.username' => ['nullable', 'string'],
            'database_config.password' => ['nullable', 'string'],
            'mail_config'          => ['nullable', 'array'],
            'mail_config.host'     => ['nullable', 'string'],
            'mail_config.port'     => ['nullable', 'integer'],
            'mail_config.encryption' => ['nullable', 'string', 'in:tls,ssl,starttls'],
            'mail_config.username' => ['nullable', 'string'],
            'mail_config.password' => ['nullable', 'string'],
            'mail_config.from_address' => ['nullable', 'email'],
            'mail_config.from_name'    => ['nullable', 'string'],
            'cache_config'         => ['nullable', 'array'],
            'cache_config.driver'  => ['nullable', 'string', 'in:redis,memcached,array,file'],
            'broker_config'        => ['nullable', 'array'],
            'metadata'             => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Tenant name is required.',
            'plan.required'  => 'Tenant plan is required.',
            'plan.in'        => 'Plan must be one of: free, starter, professional, enterprise.',
            'slug.regex'     => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique'    => 'This slug is already taken.',
            'domain.unique'  => 'This domain is already registered.',
        ];
    }
}
