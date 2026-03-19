<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email, string $tenantId): ?User
    {
        return User::forTenant($tenantId)
            ->where('email', $email)
            ->first();
    }

    public function findAllForTenant(
        string $tenantId,
        int $perPage = 15,
        array $filters = [],
    ): LengthAwarePaginator {
        $query = User::forTenant($tenantId)->orderBy('created_at', 'desc');

        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->search($filters['search']);
        }

        if (isset($filters['name']) && $filters['name'] !== '') {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['email']) && $filters['email'] !== '') {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(string $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);

        return $user->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) User::findOrFail($id)->delete();
    }

    public function search(
        string $tenantId,
        string $query,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return User::forTenant($tenantId)
            ->search($query)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function existsByEmail(
        string $email,
        string $tenantId,
        ?string $excludeId = null,
    ): bool {
        $query = User::forTenant($tenantId)->where('email', $email);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function updatePassword(string $userId, string $hashedPassword): void
    {
        User::where('id', $userId)->update(['password' => $hashedPassword]);
    }

    public function toggleStatus(string $userId, bool $isActive): void
    {
        User::where('id', $userId)->update(['is_active' => $isActive]);
    }
}
