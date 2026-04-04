<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Modules\Authorization\Application\Contracts\CheckUserPermissionServiceInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;

class CheckUserPermissionService implements CheckUserPermissionServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $userRoleRepository,
    ) {}

    public function execute(int $userId, int $tenantId, string $permissionSlug): bool
    {
        return $this->userRoleRepository->hasPermission($userId, $tenantId, $permissionSlug);
    }
}
