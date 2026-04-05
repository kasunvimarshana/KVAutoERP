<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Modules\Configuration\Domain\Entities\OrgUnit;

interface OrgUnitRepositoryInterface
{
    public function findById(int $id): ?OrgUnit;

    /** @return OrgUnit[] */
    public function findByTenantId(int $tenantId): array;

    public function create(array $data): OrgUnit;

    public function update(int $id, array $data): ?OrgUnit;

    public function delete(int $id): bool;

    /** @return OrgUnit[] */
    public function getTree(int $tenantId): array;

    /** @return OrgUnit[] */
    public function getDescendants(int $id): array;

    /** @return int[] */
    public function getDescendantIds(int $id): array;

    /** @return OrgUnit[] */
    public function getAncestors(int $id): array;
}
