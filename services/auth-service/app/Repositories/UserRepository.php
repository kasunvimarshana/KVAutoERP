<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    protected function searchableColumns(): array
    {
        return ['name', 'email'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'email', 'created_at', 'updated_at'];
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function updateLastLogin(string $id): void
    {
        $this->model->where('id', $id)->update(['last_login_at' => now()]);
    }
}
