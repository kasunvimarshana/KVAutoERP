<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model
    ) {}

    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['role'])) {
            $query->withRole($filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $sortField     = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $allowedSorts  = ['first_name', 'last_name', 'email', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    public function findByKeycloakId(string $keycloakId): ?User
    {
        return $this->model->where('keycloak_id', $keycloakId)->first();
    }

    public function create(UserDTO $dto): User
    {
        return $this->model->create($dto->toArray());
    }

    public function update(int $id, UserDTO $dto): User
    {
        $user = $this->model->findOrFail($id);
        $user->update($dto->toArray());
        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        $user = $this->model->findOrFail($id);
        return $user->delete();
    }

    public function syncWithKeycloak(string $keycloakId, array $keycloakData): User
    {
        return $this->model->updateOrCreate(
            ['keycloak_id' => $keycloakId],
            [
                'username'   => $keycloakData['preferred_username'] ?? $keycloakData['email'],
                'email'      => $keycloakData['email'],
                'first_name' => $keycloakData['given_name'] ?? '',
                'last_name'  => $keycloakData['family_name'] ?? '',
                'roles'      => $keycloakData['realm_access']['roles'] ?? ['customer'],
                'attributes' => $keycloakData['attributes'] ?? [],
                'is_active'  => true,
            ]
        );
    }
}
