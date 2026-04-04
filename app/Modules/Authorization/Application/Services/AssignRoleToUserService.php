<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Authorization\Application\Contracts\AssignRoleToUserServiceInterface;
use Modules\Authorization\Domain\Events\UserRoleAssigned;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;

class AssignRoleToUserService implements AssignRoleToUserServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $userRoleRepository,
        private readonly RoleRepositoryInterface $roleRepository,
    ) {}

    public function execute(int $userId, int $roleId, int $tenantId): void
    {
        $role = $this->roleRepository->findById($roleId);
        if ($role === null) {
            throw new RoleNotFoundException($roleId);
        }

        $this->userRoleRepository->assignRole($userId, $roleId, $tenantId);

        Event::dispatch(new UserRoleAssigned($tenantId, $userId, $roleId));
    }
}
