<?php

declare(strict_types=1);

namespace App\Application\Tenant\Commands;

/**
 * Command: Delete Tenant.
 *
 * Triggers a soft-delete of the specified tenant and optional deprovisioning.
 */
final readonly class DeleteTenantCommand
{
    /**
     * @param  string  $tenantId    Target tenant UUID.
     * @param  string  $performedBy UUID or identifier of the actor performing the deletion.
     */
    public function __construct(
        public string $tenantId,
        public string $performedBy,
    ) {}
}
