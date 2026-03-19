<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function findById(string $id): ?Role;

    public function findByName(string $name, string $tenantId): ?Role;

    public function findByTenant(string $tenantId): Collection;

    public function create(array $data): Role;

    public function update(string $id, array $data): Role;

    public function delete(string $id): bool;

    public function assignToUser(string $roleId, string $userId): void;

    public function revokeFromUser(string $roleId, string $userId): void;

    public function getUserRoles(string $userId): Collection;

    public function syncUserRoles(string $userId, array $roleIds): void;
}
