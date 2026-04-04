<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Modules\Configuration\Domain\Entities\OrgUnit;

interface OrgUnitRepositoryInterface
{
    public function findById(int $id): ?OrgUnit;

    public function findAllByTenant(int $tenantId): array;

    public function findChildren(int $parentId): array;

    public function findDescendants(int $ancestorId): array;

    public function save(OrgUnit $orgUnit): OrgUnit;

    public function delete(int $id): void;

    public function buildTree(int $tenantId): array;
}
