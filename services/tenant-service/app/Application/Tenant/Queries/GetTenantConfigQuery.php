<?php

declare(strict_types=1);

namespace App\Application\Tenant\Queries;

/**
 * Query: Get Tenant Configuration.
 *
 * Fetches one or all configuration entries for a tenant.
 * When configKey is null, all keys are returned.
 */
final readonly class GetTenantConfigQuery
{
    /**
     * @param  string       $tenantId   Target tenant UUID.
     * @param  string|null  $configKey  Specific config key, or null for all.
     * @param  string|null  $environment  Filter by environment, or null for all.
     */
    public function __construct(
        public string $tenantId,
        public ?string $configKey = null,
        public ?string $environment = null,
    ) {}
}
