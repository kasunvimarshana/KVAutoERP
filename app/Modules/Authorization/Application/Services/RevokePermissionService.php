<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Authorization\Application\Contracts\RevokePermissionServiceInterface;
use Modules\Authorization\Domain\Events\PermissionRevoked;
use Modules\Authorization\Domain\Exceptions\PermissionNotFoundException;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\PermissionRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;

class RevokePermissionService implements RevokePermissionServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly PermissionRepositoryInterface $permissionRepository,
    ) {}

    public function execute(int $roleId, int $permissionId): void
    {
        $role = $this->roleRepository->findById($roleId);
        if ($role === null) {
            throw new RoleNotFoundException($roleId);
        }

        $permission = $this->permissionRepository->findById($permissionId);
        if ($permission === null) {
            throw new PermissionNotFoundException($permissionId);
        }

        $this->roleRepository->revokePermission($roleId, $permissionId);

        Event::dispatch(new PermissionRevoked($role->tenantId, $roleId, $permissionId));
    }
}
