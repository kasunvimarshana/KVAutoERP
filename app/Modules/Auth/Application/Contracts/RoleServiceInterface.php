<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

use Illuminate\Support\Collection;

interface RoleServiceInterface
{
    public function create(array $data): mixed;
    public function update(int|string $id, array $data): mixed;
    public function delete(int|string $id, int $tenantId): bool;
    public function find(int|string $id, int $tenantId): mixed;
    public function findByTenant(int $tenantId): Collection;
    public function findBySlug(string $slug, int $tenantId): mixed;
    public function assignPermission(int|string $roleId, int|string $permissionId): void;
    public function syncPermissions(int|string $roleId, array $permissionIds): void;
}
