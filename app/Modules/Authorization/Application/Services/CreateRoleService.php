<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Authorization\Application\Contracts\CreateRoleServiceInterface;
use Modules\Authorization\Application\DTOs\CreateRoleData;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\Events\RoleCreated;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;

class CreateRoleService implements CreateRoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(CreateRoleData $data): Role
    {
        $role = new Role(
            id: null,
            tenantId: $data->tenantId,
            name: $data->name,
            slug: $data->slug,
            description: $data->description,
            isSystem: $data->isSystem,
            createdAt: null,
            updatedAt: null,
        );

        $saved = $this->repository->save($role);

        Event::dispatch(new RoleCreated($saved->tenantId, $saved->id));

        return $saved;
    }
}
