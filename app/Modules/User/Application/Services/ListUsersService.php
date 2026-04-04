<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Modules\User\Application\Contracts\ListUsersServiceInterface;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class ListUsersService implements ListUsersServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId, int $page = 1, int $perPage = 15): array
    {
        return $this->repository->findAllByTenant($tenantId, $page, $perPage);
    }
}
