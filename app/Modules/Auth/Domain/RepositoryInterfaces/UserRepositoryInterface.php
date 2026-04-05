<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\RepositoryInterfaces;

use Modules\Auth\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email, ?int $tenantId = null): ?User;

    public function create(array $data): User;

    public function update(int $id, array $data): ?User;

    public function delete(int $id): bool;

    /** @return User[] */
    public function allForTenant(int $tenantId): array;

    public function assignRole(int $userId, int $roleId): void;

    public function removeRole(int $userId, int $roleId): void;
}
