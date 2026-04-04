<?php

declare(strict_types=1);

namespace Modules\User\Domain\RepositoryInterfaces;

use Modules\User\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findAllByTenant(int $tenantId, int $page = 1, int $perPage = 15): array;

    public function save(User $user): User;

    public function delete(int $id): void;

    public function verifyPassword(int $userId, string $password): bool;

    public function changePassword(int $userId, string $newHashedPassword): void;

    public function updateAvatar(int $userId, string $avatarPath): void;
}
