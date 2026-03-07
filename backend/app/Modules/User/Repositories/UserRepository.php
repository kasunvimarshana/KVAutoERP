<?php

namespace App\Modules\User\Repositories;

use App\Core\Repository\BaseRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->where('tenant_id', $tenantId)->get();
    }

    public function findWithRoles(int $id): ?User
    {
        return $this->model->with(['roles', 'permissions', 'tenant'])->find($id);
    }
}
