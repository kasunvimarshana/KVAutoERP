<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Configuration\Domain\Entities\OrgUnit;

interface OrgUnitRepositoryInterface
{
    public function findById(int $id): ?OrgUnit;

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function insertNode(array $data, ?int $parentId): OrgUnit;

    public function updateNode(int $id, array $data): OrgUnit;

    public function deleteNode(int $id): bool;

    public function getDescendants(int $id): array;

    public function getAncestors(int $id): array;

    public function move(int $id, ?int $newParentId): OrgUnit;

    public function getTree(int $tenantId): array;
}
