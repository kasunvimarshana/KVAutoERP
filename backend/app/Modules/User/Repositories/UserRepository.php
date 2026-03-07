<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id, string $tenantId): ?User
    {
        return User::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function findByEmail(string $email, string $tenantId): ?User
    {
        return User::where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function findByKeycloakId(string $keycloakId): ?User
    {
        return User::where('keycloak_id', $keycloakId)->first();
    }

    public function paginate(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = User::where('tenant_id', $tenantId);

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function restore(string $id): bool
    {
        return (bool) User::withTrashed()->where('id', $id)->restore();
    }
}
