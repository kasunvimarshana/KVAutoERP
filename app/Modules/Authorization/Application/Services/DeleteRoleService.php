<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Authorization\Application\Contracts\DeleteRoleServiceInterface;
use Modules\Authorization\Domain\Events\RoleDeleted;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;

class DeleteRoleService implements DeleteRoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $role = $this->repository->findById($id);
        if ($role === null) {
            throw new RoleNotFoundException($id);
        }

        Event::dispatch(new RoleDeleted($role->tenantId, $id));

        $this->repository->delete($id);
    }
}
