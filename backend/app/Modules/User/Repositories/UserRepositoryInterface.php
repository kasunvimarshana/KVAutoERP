<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(string $id, string $tenantId): ?User;

    public function findByEmail(string $email, string $tenantId): ?User;

    public function findByKeycloakId(string $keycloakId): ?User;

    public function paginate(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    public function restore(string $id): bool;
}
