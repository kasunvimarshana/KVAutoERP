<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Application\Contracts\SyncRolePermissionsServiceInterface;
use Modules\Auth\Domain\Events\PermissionAssigned;
use Modules\Auth\Domain\Exceptions\RoleNotFoundException;
use Modules\Auth\Domain\Repositories\RoleRepositoryInterface;

class SyncRolePermissionsService implements SyncRolePermissionsServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(int $roleId, array $permissionIds): void
    {
        DB::transaction(function () use ($roleId, $permissionIds): void {
            $role = $this->repository->findById($roleId);

            if ($role === null) {
                throw new RoleNotFoundException($roleId);
            }

            $this->repository->syncPermissions($roleId, $permissionIds);

            Event::dispatch(new PermissionAssigned($roleId, $role->tenantId, $permissionIds));
        });
    }
}
