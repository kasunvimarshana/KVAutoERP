<?php

declare(strict_types=1);

namespace Modules\OrgUnit\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\OrgUnit\Domain\Entities\OrgUnit;

interface OrgUnitServiceInterface
{
    public function findById(int $id): ?OrgUnit;

    public function findByCode(int $tenantId, string $code): ?OrgUnit;

    /** @return Collection<int, OrgUnit> */
    public function findByTenant(int $tenantId): Collection;

    public function create(array $data): OrgUnit;

    public function update(int $id, array $data): ?OrgUnit;

    public function delete(int $id): bool;

    /**
     * Returns a flat collection ordered by materialized path (breadth-first tree order).
     *
     * @return Collection<int, OrgUnit>
     */
    public function getTree(int $tenantId): Collection;

    /** @return Collection<int, OrgUnit> */
    public function getDescendants(int $orgUnitId): Collection;

    /** @return Collection<int, OrgUnit> */
    public function getAncestors(int $orgUnitId): Collection;

    /**
     * Moves the org unit to a new parent, recomputing paths and levels
     * for the unit and all its descendants.
     */
    public function move(int $orgUnitId, ?int $newParentId): OrgUnit;
}
