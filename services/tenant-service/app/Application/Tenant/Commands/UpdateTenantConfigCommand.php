<?php

declare(strict_types=1);

namespace App\Application\Tenant\Commands;

/**
 * Command: Update Tenant Configuration.
 *
 * Creates or updates a single runtime configuration key for a tenant.
 */
final readonly class UpdateTenantConfigCommand
{
    /**
     * @param  string  $tenantId     Target tenant UUID.
     * @param  string  $configKey    Dot-notation configuration key.
     * @param  mixed   $configValue  Value to persist (arrays are JSON-encoded).
     * @param  string  $environment  Target environment (testing|staging|production).
     */
    public function __construct(
        public string $tenantId,
        public string $configKey,
        public mixed $configValue,
        public string $environment = 'production',
    ) {}
}
