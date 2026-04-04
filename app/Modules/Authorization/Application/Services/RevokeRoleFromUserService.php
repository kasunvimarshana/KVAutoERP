<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Authorization\Application\Contracts\RevokeRoleFromUserServiceInterface;
use Modules\Authorization\Domain\Events\UserRoleRevoked;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;

class RevokeRoleFromUserService implements RevokeRoleFromUserServiceInterface
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

        $this->userRoleRepository->revokeRole($userId, $roleId, $tenantId);

        Event::dispatch(new UserRoleRevoked($tenantId, $userId, $roleId));
    }
}
