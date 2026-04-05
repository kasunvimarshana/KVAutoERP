<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\RepositoryInterfaces;

use Modules\Auth\Domain\Entities\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;

    public function findBySlug(string $slug, ?int $tenantId = null): ?Role;

    public function create(array $data): Role;

    public function update(int $id, array $data): ?Role;

    public function delete(int $id): bool;

    /** @return Role[] */
    public function allForTenant(?int $tenantId): array;

    public function syncPermissions(int $roleId, array $permissions): void;
}
