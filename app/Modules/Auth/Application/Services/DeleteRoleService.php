<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Application\Contracts\DeleteRoleServiceInterface;
use Modules\Auth\Domain\Events\RoleDeleted;
use Modules\Auth\Domain\Exceptions\RoleNotFoundException;
use Modules\Auth\Domain\Repositories\RoleRepositoryInterface;

class DeleteRoleService implements DeleteRoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $role = $this->repository->findById($id);

            if ($role === null) {
                throw new RoleNotFoundException($id);
            }

            $result = $this->repository->delete($id);

            if ($result) {
                Event::dispatch(new RoleDeleted($id, $role->tenantId));
            }

            return $result;
        });
    }
}
