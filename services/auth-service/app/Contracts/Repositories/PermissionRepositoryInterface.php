<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    public function findById(string $id): ?Permission;

    public function findByName(string $name): ?Permission;

    public function findByTenant(string $tenantId): Collection;

    public function create(array $data): Permission;

    public function update(string $id, array $data): Permission;

    public function delete(string $id): bool;

    public function assignToRole(string $permissionId, string $roleId): void;

    public function revokeFromRole(string $permissionId, string $roleId): void;

    public function assignDirectlyToUser(string $permissionId, string $userId): void;

    public function revokeDirectlyFromUser(string $permissionId, string $userId): void;

    public function getUserPermissions(string $userId): Collection;

    public function syncRolePermissions(string $roleId, array $permissionIds): void;
}
