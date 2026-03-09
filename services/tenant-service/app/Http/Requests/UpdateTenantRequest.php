<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Shared\Base\BaseRequest;

/**
 * Update Tenant Request.
 *
 * Validates partial update fields for an existing tenant.
 * All fields are optional — only provided fields are updated.
 */
final class UpdateTenantRequest extends BaseRequest
{
    /**
     * @return array<string, string|array<string>>
     */
    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'min:2', 'max:255'],
            'domain'        => ['sometimes', 'nullable', 'url', 'max:255'],
            'plan'          => ['sometimes', 'string', 'in:starter,pro,enterprise'],
            'billing_email' => ['sometimes', 'email', 'max:255'],
            'is_active'     => ['sometimes', 'boolean'],
            'settings'      => ['sometimes', 'nullable', 'array'],
            'settings.*'    => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plan.in' => 'Plan must be one of: starter, pro, enterprise.',
        ];
    }
}
