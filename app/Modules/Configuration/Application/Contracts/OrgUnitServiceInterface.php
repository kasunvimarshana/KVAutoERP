<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\OrgUnit;

interface OrgUnitServiceInterface
{
    public function createOrgUnit(array $data): OrgUnit;
    public function updateOrgUnit(string $id, array $data): OrgUnit;
    public function deleteOrgUnit(string $id): bool;
    public function getOrgUnit(string $id): OrgUnit;
    public function getTree(string $tenantId): Collection;
    public function getDescendants(string $id): Collection;
    public function getAncestors(string $id): Collection;
    public function moveOrgUnit(string $id, ?string $newParentId): OrgUnit;
}
