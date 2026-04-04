<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Application\Contracts\AssignUserRoleServiceInterface;
use Modules\Auth\Application\DTOs\AssignUserRoleData;
use Modules\Auth\Domain\Entities\UserRole;
use Modules\Auth\Domain\Events\UserRoleAssigned;
use Modules\Auth\Domain\Repositories\UserRoleRepositoryInterface;

class AssignUserRoleService implements AssignUserRoleServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $repository,
    ) {}

    public function execute(AssignUserRoleData $data): UserRole
    {
        return DB::transaction(function () use ($data): UserRole {
            $userRole = $this->repository->assign($data->userId, $data->roleId, $data->tenantId);

            Event::dispatch(new UserRoleAssigned($data->userId, $data->roleId, $data->tenantId));

            return $userRole;
        });
    }
}
