<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->newQuery()->where('email', $email)->first();
    }

    public function findByTenant(string $tenantId): Collection
    {
        return $this->withTenant($tenantId)->all();
    }

    public function getByRole(string $role): Collection
    {
        return $this->newQuery()->where('role', $role)->get();
    }

    public function getWithPagination(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->newQuery()->paginate($perPage, ['*'], 'page', $page);
    }
}
