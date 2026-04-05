<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Domain\Entities\OrgUnit;

interface OrgUnitServiceInterface
{
    public function create(array $data): OrgUnit;

    public function update(int $id, array $data): OrgUnit;

    public function delete(int $id): bool;

    /** @return OrgUnit[] */
    public function getTree(int $tenantId): array;

    /** @return OrgUnit[] */
    public function getDescendants(int $id): array;

    /** @return OrgUnit[] */
    public function getAncestors(int $id): array;

    public function move(int $id, ?int $newParentId): OrgUnit;
}
