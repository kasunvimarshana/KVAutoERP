<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function create(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            return $this->repository->create($data);
        });
    }

    public function update(int|string $id, array $data): mixed
    {
        return DB::transaction(function () use ($id, $data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $updated = $this->repository->update($id, $data);
            if (! $updated) {
                throw new NotFoundException('User', $id);
            }

            return $updated;
        });
    }

    public function delete(int|string $id, int $tenantId): bool
    {
        return DB::transaction(function () use ($id, $tenantId) {
            $user = $this->repository->findById($id, $tenantId);
            if (! $user) {
                throw new NotFoundException('User', $id);
            }

            return $this->repository->delete($id);
        });
    }

    public function find(int|string $id, int $tenantId): mixed
    {
        return $this->repository->findById($id, $tenantId);
    }

    public function findByEmail(string $email, int $tenantId): mixed
    {
        return $this->repository->findByEmail($email, $tenantId);
    }

    public function listUsers(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->findAllByTenant($tenantId, $perPage, $page);
    }

    public function assignRole(int|string $userId, int|string $roleId): void
    {
        DB::transaction(function () use ($userId, $roleId) {
            $user = $this->repository->find($userId);
            if (! $user) {
                throw new NotFoundException('User', $userId);
            }

            $user->roles()->syncWithoutDetaching([$roleId]);
        });
    }

    public function removeRole(int|string $userId, int|string $roleId): void
    {
        DB::transaction(function () use ($userId, $roleId) {
            $user = $this->repository->find($userId);
            if (! $user) {
                throw new NotFoundException('User', $userId);
            }

            $user->roles()->detach($roleId);
        });
    }

    public function syncRoles(int|string $userId, array $roleIds): void
    {
        DB::transaction(function () use ($userId, $roleIds) {
            $user = $this->repository->find($userId);
            if (! $user) {
                throw new NotFoundException('User', $userId);
            }

            $user->roles()->sync($roleIds);
        });
    }
}
