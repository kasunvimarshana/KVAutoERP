<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Services;

use Modules\Authorization\Application\Contracts\ListRolesServiceInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;

class ListRolesService implements ListRolesServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId): array
    {
        return $this->repository->findAllByTenant($tenantId);
    }
}
