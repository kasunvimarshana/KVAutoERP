<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Tenant\Application\Contracts\ListTenantsServiceInterface;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use Modules\Tenant\Domain\ValueObjects\TenantStatus;

class ListTenantsService implements ListTenantsServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        if (isset($filters['status'])) {
            return $this->repository->findByStatus(
                TenantStatus::from($filters['status']),
                $perPage,
                $page,
            );
        }

        return $this->repository->findAll($perPage, $page);
    }
}
