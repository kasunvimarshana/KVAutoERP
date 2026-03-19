<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;

/**
 * Eloquent-backed user repository.
 */
final class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function findById(string $id): ?User
    {
        return User::withoutGlobalScopes()->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmailAndTenant(string $email, string $tenantId): ?User
    {
        return User::withoutGlobalScopes()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmail(string $email): ?User
    {
        return User::withoutGlobalScopes()
            ->where('email', $email)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): User
    {
        return User::withoutGlobalScopes()->create($data);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotFoundException When the user does not exist.
     */
    public function update(string $id, array $data): User
    {
        $user = $this->findById($id);

        if ($user === null) {
            throw new NotFoundException("User [{$id}] not found.");
        }

        $user->fill($data)->save();

        return $user->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function incrementTokenVersion(string $userId): int
    {
        $user = $this->findById($userId);

        if ($user === null) {
            return 1;
        }

        $user->increment('token_version');

        return $user->fresh()->token_version;
    }

    /**
     * {@inheritDoc}
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: $perPage, page: $page);
    }

    /**
     * {@inheritDoc}
     */
    public function existsByEmailAndTenant(string $email, string $tenantId, ?string $excludeId = null): bool
    {
        $query = User::withoutGlobalScopes()
            ->where('email', $email)
            ->where('tenant_id', $tenantId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
