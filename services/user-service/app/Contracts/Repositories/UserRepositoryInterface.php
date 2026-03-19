<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email, string $tenantId): ?User;

    public function findAllForTenant(
        string $tenantId,
        int $perPage = 15,
        array $filters = [],
    ): LengthAwarePaginator;

    public function create(array $data): User;

    public function update(string $id, array $data): User;

    public function delete(string $id): bool;

    public function search(
        string $tenantId,
        string $query,
        int $perPage = 15,
    ): LengthAwarePaginator;

    public function existsByEmail(string $email, string $tenantId, ?string $excludeId = null): bool;

    public function updatePassword(string $userId, string $hashedPassword): void;

    public function toggleStatus(string $userId, bool $isActive): void;
}
