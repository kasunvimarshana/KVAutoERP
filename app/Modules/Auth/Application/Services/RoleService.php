<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Application\Contracts\RoleServiceInterface;
use Modules\Auth\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function create(array $data): mixed
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    public function update(int|string $id, array $data): mixed
    {
        return DB::transaction(function () use ($id, $data) {
            $updated = $this->repository->update($id, $data);
            if (! $updated) {
                throw new NotFoundException('Role', $id);
            }

            return $updated;
        });
    }

    public function delete(int|string $id, int $tenantId): bool
    {
        return DB::transaction(function () use ($id, $tenantId) {
            $role = $this->repository->findById($id, $tenantId);
            if (! $role) {
                throw new NotFoundException('Role', $id);
            }

            return $this->repository->delete($id);
        });
    }

    public function find(int|string $id, int $tenantId): mixed
    {
        return $this->repository->findById($id, $tenantId);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->repository->findByTenant($tenantId);
    }

    public function findBySlug(string $slug, int $tenantId): mixed
    {
        return $this->repository->findBySlug($slug, $tenantId);
    }

    public function assignPermission(int|string $roleId, int|string $permissionId): void
    {
        DB::transaction(function () use ($roleId, $permissionId) {
            $role = $this->repository->find($roleId);
            if (! $role) {
                throw new NotFoundException('Role', $roleId);
            }

            $role->permissions()->syncWithoutDetaching([$permissionId]);
        });
    }

    public function syncPermissions(int|string $roleId, array $permissionIds): void
    {
        DB::transaction(function () use ($roleId, $permissionIds) {
            $role = $this->repository->find($roleId);
            if (! $role) {
                throw new NotFoundException('Role', $roleId);
            }

            $role->permissions()->sync($permissionIds);
        });
    }
}
