<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\OrgUnit;

interface OrgUnitRepositoryInterface
{
    public function findById(string $id): ?OrgUnit;
    public function findByCode(string $code, string $tenantId): ?OrgUnit;
    public function allByTenant(string $tenantId): Collection;
    public function getTree(string $tenantId): Collection;
    public function getDescendants(string $id): Collection;
    public function getAncestors(string $id): Collection;
    public function create(array $data): OrgUnit;
    public function update(string $id, array $data): OrgUnit;
    public function delete(string $id): bool;
    public function move(string $id, ?string $newParentId): OrgUnit;
}
