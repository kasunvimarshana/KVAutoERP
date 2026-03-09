<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories\Interfaces;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Tenant Repository Interface.
 *
 * Defines the contract for Tenant data access operations.
 */
interface TenantRepositoryInterface
{
    /**
     * Retrieve all tenants with optional filtering and pagination.
     *
     * @param  array<string, mixed>                     $params
     * @return LengthAwarePaginator|Collection<int, Tenant>
     */
    public function all(array $params = []): LengthAwarePaginator|Collection;

    /**
     * Find a tenant by its primary key.
     *
     * @param  string $id
     * @return Tenant|null
     */
    public function find(string $id): ?Tenant;

    /**
     * Find a tenant by its slug.
     *
     * @param  string $slug
     * @return Tenant|null
     */
    public function findBySlug(string $slug): ?Tenant;

    /**
     * Find a tenant by its domain.
     *
     * @param  string $domain
     * @return Tenant|null
     */
    public function findByDomain(string $domain): ?Tenant;

    /**
     * Create a new tenant.
     *
     * @param  array<string, mixed> $data
     * @return Tenant
     */
    public function create(array $data): Tenant;

    /**
     * Update an existing tenant.
     *
     * @param  string               $id
     * @param  array<string, mixed> $data
     * @return Tenant
     */
    public function update(string $id, array $data): Tenant;

    /**
     * Update the tenant's runtime configuration.
     *
     * @param  string               $id
     * @param  array<string, mixed> $config
     * @return Tenant
     */
    public function updateConfiguration(string $id, array $config): Tenant;

    /**
     * Delete a tenant and all its data.
     *
     * @param  string $id
     * @return bool
     */
    public function delete(string $id): bool;
}
