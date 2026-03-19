<?php

declare(strict_types=1);

namespace App\Contracts;

interface RoleServiceContract
{
    public function findById(string $roleId): ?array;

    public function create(array $data): array;

    public function update(string $roleId, array $data): array;

    public function delete(string $roleId): void;

    public function assignRole(string $userId, string $roleId, ?string $tenantId = null): void;

    public function revokeRole(string $userId, string $roleId, ?string $tenantId = null): void;

    public function getUserRoles(string $userId, ?string $tenantId = null): array;

    public function listForTenant(string $tenantId): array;

    public function syncPermissions(string $roleId, array $permissionIds): void;
}
