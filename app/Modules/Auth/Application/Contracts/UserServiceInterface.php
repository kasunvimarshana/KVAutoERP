<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function create(array $data): mixed;
    public function update(int|string $id, array $data): mixed;
    public function delete(int|string $id, int $tenantId): bool;
    public function find(int|string $id, int $tenantId): mixed;
    public function findByEmail(string $email, int $tenantId): mixed;
    public function listUsers(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator;
    public function assignRole(int|string $userId, int|string $roleId): void;
    public function removeRole(int|string $userId, int|string $roleId): void;
    public function syncRoles(int|string $userId, array $roleIds): void;
}
