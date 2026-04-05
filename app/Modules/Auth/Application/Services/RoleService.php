<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\RoleServiceInterface;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function create(array $data): Role
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Role
    {
        $role = $this->repository->update($id, $data);

        if ($role === null) {
            throw new NotFoundException("Role with id {$id} not found.");
        }

        return $role;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Role
    {
        $role = $this->repository->findById($id);

        if ($role === null) {
            throw new NotFoundException("Role with id {$id} not found.");
        }

        return $role;
    }

    public function allForTenant(?int $tenantId): array
    {
        return $this->repository->allForTenant($tenantId);
    }

    public function syncPermissions(int $roleId, array $permissions): void
    {
        $this->repository->syncPermissions($roleId, $permissions);
    }
}
