<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Application\Contracts\CreateRoleServiceInterface;
use Modules\Auth\Application\DTOs\CreateRoleData;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\Events\RoleCreated;
use Modules\Auth\Domain\Repositories\RoleRepositoryInterface;

class CreateRoleService implements CreateRoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(CreateRoleData $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $role = $this->repository->create([
                'tenant_id'   => $data->tenantId,
                'name'        => $data->name,
                'slug'        => $data->slug,
                'description' => $data->description,
                'is_system'   => $data->isSystem,
            ]);

            Event::dispatch(new RoleCreated($role));

            return $role;
        });
    }
}
