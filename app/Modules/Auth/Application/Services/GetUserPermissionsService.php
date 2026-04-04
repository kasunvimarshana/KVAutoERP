<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\GetUserPermissionsServiceInterface;
use Modules\Auth\Domain\Repositories\UserRoleRepositoryInterface;

class GetUserPermissionsService implements GetUserPermissionsServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, int $tenantId): array
    {
        return $this->repository->getUserPermissions($userId, $tenantId);
    }
}
