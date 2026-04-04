<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Authorization\Application\Contracts\AssignPermissionServiceInterface;
use Modules\Authorization\Domain\Events\PermissionAssigned;
use Modules\Authorization\Domain\Exceptions\PermissionNotFoundException;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\PermissionRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;

class AssignPermissionService implements AssignPermissionServiceInterface
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

        $this->roleRepository->assignPermission($roleId, $permissionId);

        Event::dispatch(new PermissionAssigned($role->tenantId, $roleId, $permissionId));
    }
}
