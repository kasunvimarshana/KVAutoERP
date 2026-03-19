<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email, string $tenantId): ?User;

    public function findByEmailGlobal(string $email): ?User;

    public function create(array $data): User;

    public function update(string $id, array $data): User;

    public function delete(string $id): bool;

    public function incrementTokenVersion(string $userId): void;

    public function incrementFailedLoginAttempts(string $userId): void;

    public function resetFailedLoginAttempts(string $userId): void;

    public function lockUser(string $userId, int $durationMinutes): void;

    public function unlockUser(string $userId): void;

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    public function existsByEmail(string $email, string $tenantId): bool;

    public function updatePassword(string $userId, string $hashedPassword): void;

    public function updateLastLoginAt(string $userId, string $ipAddress): void;
}
