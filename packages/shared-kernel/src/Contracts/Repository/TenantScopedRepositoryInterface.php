<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Repository;

use KvEnterprise\SharedKernel\DTOs\FilterDTO;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\ValueObjects\TenantId;

/**
 * Tenant-scoped repository contract.
 *
 * Extends the base repository with methods that enforce tenant isolation,
 * ensuring records are always filtered to the current tenant context.
 * All queries MUST include the tenant_id predicate.
 */
interface TenantScopedRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a single record belonging to the given tenant by its primary key.
     *
     * @param  string|int  $id        The record's primary key.
     * @param  TenantId    $tenantId  The owning tenant's identifier.
     * @return object|null             The found record or null.
     */
    public function findByTenantId(string|int $id, TenantId $tenantId): ?object;

    /**
     * Retrieve all records that belong to the given tenant.
     *
     * @param  TenantId         $tenantId  The owning tenant's identifier.
     * @param  FilterDTO|null   $filter    Optional filter/sort criteria.
     * @return array<int, object>           All matching records for the tenant.
     */
    public function findAllByTenant(TenantId $tenantId, ?FilterDTO $filter = null): array;

    /**
     * Return a paginated result set scoped to a specific tenant.
     *
     * @param  TenantId         $tenantId  The owning tenant's identifier.
     * @param  int              $page      1-based page number.
     * @param  int              $perPage   Records per page.
     * @param  FilterDTO|null   $filter    Optional filter/sort criteria.
     * @return PaginationDTO               Pagination envelope for the tenant.
     */
    public function paginateByTenant(
        TenantId $tenantId,
        int $page = 1,
        int $perPage = 15,
        ?FilterDTO $filter = null,
    ): PaginationDTO;

    /**
     * Count records belonging to the given tenant.
     *
     * @param  TenantId  $tenantId  The owning tenant's identifier.
     * @return int                   Total record count for the tenant.
     */
    public function countByTenant(TenantId $tenantId): int;
}
