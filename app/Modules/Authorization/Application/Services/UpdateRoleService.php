<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Authorization\Application\Contracts\UpdateRoleServiceInterface;
use Modules\Authorization\Application\DTOs\UpdateRoleData;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\Events\RoleUpdated;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;

class UpdateRoleService implements UpdateRoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateRoleData $data): Role
    {
        $role = $this->repository->findById($id);
        if ($role === null) {
            throw new RoleNotFoundException($id);
        }

        if ($data->name !== null) {
            $role->name = $data->name;
        }
        if ($data->slug !== null) {
            $role->slug = $data->slug;
        }
        if ($data->description !== null) {
            $role->description = $data->description;
        }

        $saved = $this->repository->save($role);

        Event::dispatch(new RoleUpdated($saved->tenantId, $saved->id));

        return $saved;
    }
}
