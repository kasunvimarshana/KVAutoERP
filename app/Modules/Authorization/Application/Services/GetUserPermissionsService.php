<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Modules\Authorization\Application\Contracts\GetUserPermissionsServiceInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;

class GetUserPermissionsService implements GetUserPermissionsServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $userRoleRepository,
        private readonly RoleRepositoryInterface $roleRepository,
    ) {}

    public function execute(int $userId, int $tenantId): array
    {
        $roles = $this->userRoleRepository->getUserRoles($userId, $tenantId);
        $permissions = [];

        foreach ($roles as $role) {
            $rolePermissions = $this->roleRepository->getPermissions($role->id);
            foreach ($rolePermissions as $permission) {
                $permissions[$permission->slug] = $permission;
            }
        }

        return array_values($permissions);
    }
}
