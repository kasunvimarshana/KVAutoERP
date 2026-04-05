<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Domain\Entities\Role;

interface RoleServiceInterface
{
    public function create(array $data): Role;

    public function update(int $id, array $data): Role;

    public function delete(int $id): bool;

    public function find(int $id): Role;

    /** @return Role[] */
    public function allForTenant(?int $tenantId): array;

    public function syncPermissions(int $roleId, array $permissions): void;
}
