<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Shared\Base\BaseRequest;

/**
 * Create Tenant Request.
 *
 * Validates the payload required to create and provision a new tenant.
 */
final class CreateTenantRequest extends BaseRequest
{
    /**
     * @return array<string, string|array<string>>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'min:2', 'max:255'],
            'slug'          => ['required', 'string', 'min:2', 'max:63', 'unique:tenants,slug', 'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/'],
            'domain'        => ['nullable', 'url', 'max:255'],
            'plan'          => ['required', 'string', 'in:starter,pro,enterprise'],
            'billing_email' => ['required', 'email', 'max:255'],
            'admin_email'   => ['required', 'email', 'max:255'],
            'settings'      => ['nullable', 'array'],
            'settings.*'    => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique'  => 'A tenant with this slug already exists.',
            'slug.regex'   => 'Slug must contain only lowercase letters, digits, and hyphens.',
            'plan.in'      => 'Plan must be one of: starter, pro, enterprise.',
        ];
    }
}
