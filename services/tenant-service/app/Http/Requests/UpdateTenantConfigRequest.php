<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Shared\Base\BaseRequest;

/**
 * Update Tenant Configuration Request.
 *
 * Validates the payload required to create or update a single config entry.
 */
final class UpdateTenantConfigRequest extends BaseRequest
{
    /**
     * @return array<string, string|array<string>>
     */
    public function rules(): array
    {
        return [
            'config_key'   => ['required', 'string', 'max:255'],
            'config_value' => ['required'],
            'environment'  => ['sometimes', 'string', 'in:testing,staging,production'],
            'is_secret'    => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'config_key.required'   => 'A configuration key is required.',
            'config_value.required' => 'A configuration value is required.',
            'environment.in'        => 'Environment must be one of: testing, staging, production.',
        ];
    }
}
