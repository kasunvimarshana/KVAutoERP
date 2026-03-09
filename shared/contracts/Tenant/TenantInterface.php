<?php

declare(strict_types=1);

namespace Shared\Contracts\Tenant;

/**
 * Tenant Interface.
 *
 * Defines the contract for tenant entities across all services.
 */
interface TenantInterface
{
    /**
     * Get the tenant's unique identifier.
     *
     * @return string
     */
    public function getTenantId(): string;

    /**
     * Get the tenant's slug (used in subdomains/URL routing).
     *
     * @return string
     */
    public function getSlug(): string;

    /**
     * Get the tenant's database connection name.
     *
     * @return string
     */
    public function getDatabaseConnection(): string;

    /**
     * Get the tenant's runtime configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfiguration(): array;

    /**
     * Check if the tenant is active.
     *
     * @return bool
     */
    public function isActive(): bool;
}
