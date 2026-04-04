<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Application\Contracts\RevokeUserRoleServiceInterface;
use Modules\Auth\Domain\Events\UserRoleRevoked;
use Modules\Auth\Domain\Repositories\UserRoleRepositoryInterface;

class RevokeUserRoleService implements RevokeUserRoleServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, int $roleId, int $tenantId): bool
    {
        return DB::transaction(function () use ($userId, $roleId, $tenantId): bool {
            $result = $this->repository->revoke($userId, $roleId, $tenantId);

            if ($result) {
                Event::dispatch(new UserRoleRevoked($userId, $roleId, $tenantId));
            }

            return $result;
        });
    }
}
