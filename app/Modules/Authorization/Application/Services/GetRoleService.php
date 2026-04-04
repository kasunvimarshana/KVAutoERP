<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Modules\Authorization\Application\Contracts\GetRoleServiceInterface;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;

class GetRoleService implements GetRoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(int $id): Role
    {
        $role = $this->repository->findById($id);
        if ($role === null) {
            throw new RoleNotFoundException($id);
        }

        return $role;
    }
}
