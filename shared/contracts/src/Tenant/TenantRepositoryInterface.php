<?php

declare(strict_types=1);

namespace Saas\Contracts\Tenant;

/**
 * Defines the persistence contract for tenant lookups.
 *
 * Implementations may back this interface with a database, cache, an external
 * identity provider, or any combination thereof.
 */
interface TenantRepositoryInterface
{
    /**
     * Retrieves a tenant by its unique identifier.
     *
     * @param string $id UUID or ULID of the tenant.
     *
     * @return TenantInterface|null The matching tenant, or `null` when not found.
     */
    public function findById(string $id): ?TenantInterface;

    /**
     * Retrieves a tenant by its registered domain.
     *
     * @param string $domain Fully-qualified domain name, e.g. `acme.example.com`.
     *
     * @return TenantInterface|null The matching tenant, or `null` when not found.
     */
    public function findByDomain(string $domain): ?TenantInterface;

    /**
     * Retrieves a tenant by its URL slug.
     *
     * @param string $slug URL-safe identifier, e.g. `acme-corp`.
     *
     * @return TenantInterface|null The matching tenant, or `null` when not found.
     */
    public function findBySlug(string $slug): ?TenantInterface;

    /**
     * Returns all tenants known to the system.
     *
     * Implementations should apply sensible ordering (e.g. by name or creation
     * date) and avoid loading unbounded result sets in production contexts;
     * callers that need pagination should use the dedicated repository paginator.
     *
     * @return TenantInterface[]
     */
    public function findAll(): array;
}
