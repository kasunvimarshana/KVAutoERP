<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\User\Application\Contracts\ListUsersServiceInterface;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class ListUsersService implements ListUsersServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        if (isset($filters['tenant_id'])) {
            return $this->repository->findByTenant((int) $filters['tenant_id'], $perPage, $page);
        }

        return $this->repository->findByTenant(0, $perPage, $page);
    }
}
