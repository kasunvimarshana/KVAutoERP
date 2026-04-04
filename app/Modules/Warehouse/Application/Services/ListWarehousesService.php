<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Warehouse\Application\Contracts\ListWarehousesServiceInterface;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;

class ListWarehousesService implements ListWarehousesServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->findByTenant($tenantId, $perPage, $page);
    }
}
