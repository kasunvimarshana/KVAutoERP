<?php

declare(strict_types=1);

namespace App\Application\Tenant\Queries;

/**
 * Query: Get Tenant.
 *
 * Fetches a single tenant by its UUID.
 */
final readonly class GetTenantQuery
{
    /**
     * @param  string  $tenantId  Target tenant UUID.
     */
    public function __construct(
        public string $tenantId,
    ) {}
}
