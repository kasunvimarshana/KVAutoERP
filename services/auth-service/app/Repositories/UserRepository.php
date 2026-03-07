<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->newQuery()->where('email', $email)->first();
    }

    public function findByEmailAndTenant(string $email, string $tenantId): ?User
    {
        /** @var User|null */
        return $this->newQuery()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function findByTenant(string $tenantId): Collection
    {
        return $this->withTenant($tenantId)->all();
    }

    public function getByRole(string $role, ?string $tenantId = null): Collection
    {
        $query = $this->newQuery()->where('role', $role);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get();
    }

    public function findActiveByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->newQuery()
            ->where('email', $email)
            ->where('status', 'active')
            ->first();
    }

    public function countByTenant(string $tenantId): int
    {
        return $this->newQuery()->where('tenant_id', $tenantId)->count();
    }
}
