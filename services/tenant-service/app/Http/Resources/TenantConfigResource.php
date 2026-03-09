<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Shared\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * Tenant Configuration API Resource.
 *
 * Serialises a TenantConfiguration entry.
 * Secret values are masked in the output.
 */
final class TenantConfigResource extends BaseResource
{
    /** Mask string used in place of secret config values. */
    private const SECRET_MASK = '***';

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    protected function resourceData(Request $request): array
    {
        $isSecret    = (bool) $this->attr('is_secret', false);
        $configValue = $this->attr('config_value');

        return [
            'tenant_id'    => $this->attr('tenant_id'),
            'config_key'   => $this->attr('config_key'),
            'config_value' => $isSecret ? self::SECRET_MASK : $configValue,
            'environment'  => $this->attr('environment', 'production'),
            'is_secret'    => $isSecret,
        ];
    }
}
