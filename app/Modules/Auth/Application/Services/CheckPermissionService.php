<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\CheckPermissionServiceInterface;
use Modules\Auth\Domain\Repositories\UserRoleRepositoryInterface;

class CheckPermissionService implements CheckPermissionServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, string $ability, int $tenantId): bool
    {
        $permissions = $this->repository->getUserPermissions($userId, $tenantId);

        return in_array($ability, $permissions, true);
    }
}
