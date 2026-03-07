<?php

namespace App\Modules\User\Services;

use App\Core\MessageBroker\MessageBrokerInterface;
use App\Core\Pagination\PaginationHelper;
use App\Core\Service\BaseService;
use App\Core\Tenant\TenantManager;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(
        UserRepository $repository,
        private MessageBrokerInterface $broker,
        private TenantManager $tenantManager
    ) {
        parent::__construct($repository);
    }

    public function index(array $params = []): array
    {
        $query = $this->repository->query()->with(['roles', 'permissions', 'tenant']);
        $this->applyFilters($query, $params);

        return PaginationHelper::paginate($query, $params);
    }

    public function store(array $data): Model
    {
        if ($this->tenantManager->hasTenant()) {
            $data['tenant_id'] = $this->tenantManager->getTenantId();
        }

        $user = $this->repository->create($data);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        } else {
            $user->assignRole('user');
        }

        $this->broker->publish('user.created', [
            'user_id'   => $user->id,
            'tenant_id' => $user->tenant_id,
            'email'     => $user->email,
        ]);

        return $user->load('roles');
    }

    public function update(int $id, array $data): Model
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->repository->update($id, $data);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        $this->broker->publish('user.updated', [
            'user_id'   => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);

        return $user->load('roles');
    }

    public function destroy(int $id): bool
    {
        $user     = $this->repository->findByIdOrFail($id);
        $userId   = $user->id;
        $tenantId = $user->tenant_id;
        $result   = $this->repository->delete($id);

        $this->broker->publish('user.deleted', [
            'user_id'   => $userId,
            'tenant_id' => $tenantId,
        ]);

        return $result;
    }

    public function assignRole(int $userId, string $role): Model
    {
        $user = $this->repository->findByIdOrFail($userId);
        $user->assignRole($role);

        return $user->load('roles');
    }

    public function revokeRole(int $userId, string $role): Model
    {
        $user = $this->repository->findByIdOrFail($userId);
        $user->removeRole($role);

        return $user->load('roles');
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        if (!empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->where('name', 'like', "%{$params['search']}%")
                  ->orWhere('email', 'like', "%{$params['search']}%");
            });
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', filter_var($params['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($params['role'])) {
            $query->role($params['role']);
        }
    }
}
