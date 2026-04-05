<?php

declare(strict_types=1);

namespace Modules\OrgUnit\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\OrgUnit\Domain\Entities\OrgUnit;

interface OrgUnitRepositoryInterface
{
    public function findById(int $id): ?OrgUnit;

    public function findByCode(int $tenantId, string $code): ?OrgUnit;

    /** @return Collection<int, OrgUnit> */
    public function findByTenant(int $tenantId): Collection;

    public function create(array $data): OrgUnit;

    public function update(int $id, array $data): ?OrgUnit;

    public function delete(int $id): bool;

    /**
     * Returns a flat collection of all org units for a tenant, ordered by path,
     * suitable for building a tree client-side or via recursive processing.
     *
     * @return Collection<int, OrgUnit>
     */
    public function getTree(int $tenantId): Collection;

    /**
     * Returns all descendants of the given org unit using a path LIKE query.
     *
     * @return Collection<int, OrgUnit>
     */
    public function getDescendants(int $orgUnitId): Collection;

    /**
     * Returns all ancestors of the given org unit by parsing its materialized path.
     *
     * @return Collection<int, OrgUnit>
     */
    public function getAncestors(int $orgUnitId): Collection;

    /**
     * Moves the given org unit under a new parent, updating the path and level
     * for the unit itself and all of its descendants.
     */
    public function move(int $orgUnitId, ?int $newParentId): OrgUnit;
}
