<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use App\Shared\Contracts\RepositoryInterface;

/**
 * Tenant Repository Interface.
 *
 * Extends the generic RepositoryInterface with Tenant-specific query methods.
 */
interface TenantRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a tenant by its URL-safe slug.
     *
     * @param  string  $slug  Tenant slug (e.g. "acme-corp").
     * @return Tenant|null    Hydrated entity or null if not found.
     */
    public function findBySlug(string $slug): ?Tenant;

    /**
     * Find a tenant by its custom domain.
     *
     * @param  string  $domain  Fully-qualified domain name.
     * @return Tenant|null
     */
    public function findByDomain(string $domain): ?Tenant;

    /**
     * Return all active (non-deleted, is_active = true) tenants.
     *
     * @return array<int, Tenant>
     */
    public function findActive(): array;

    /**
     * Persist or update a single runtime configuration key for a tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @param  string  $key       Configuration key (dot-notation accepted).
     * @param  mixed   $value     Scalar or array value; arrays are JSON-encoded.
     */
    public function updateConfiguration(string $tenantId, string $key, mixed $value): void;

    /**
     * Retrieve a single configuration value for a tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @param  string  $key       Configuration key.
     * @return mixed              Raw typed value, or null if not found.
     */
    public function getConfiguration(string $tenantId, string $key): mixed;

    /**
     * Retrieve all configuration entries for a tenant as a key→value map.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return array<string, mixed>
     */
    public function getAllConfigurations(string $tenantId): array;

    /**
     * Create an isolated database for the tenant and run initial setup.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return bool               True when the database was created successfully.
     */
    public function provisionDatabase(string $tenantId): bool;

    /**
     * Drop the tenant's isolated database.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return bool               True when the database was removed successfully.
     */
    public function deleteDatabase(string $tenantId): bool;
}
