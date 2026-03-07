<?php

namespace App\Modules\User\Repositories\Interfaces;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    public function findByKeycloakId(string $keycloakId): ?User;

    public function create(UserDTO $dto): User;

    public function update(int $id, UserDTO $dto): User;

    public function delete(int $id): bool;

    public function syncWithKeycloak(string $keycloakId, array $keycloakData): User;
}
