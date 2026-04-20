<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Warehouse\Application\Contracts\FindWarehouseStockMovementServiceInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseStockMovementRepositoryInterface;

class FindWarehouseStockMovementService extends BaseService implements FindWarehouseStockMovementServiceInterface
{
    public function __construct(
        private readonly WarehouseStockMovementRepositoryInterface $stockMovementRepository,
        WarehouseRepositoryInterface $warehouseRepository,
    ) {
        parent::__construct($warehouseRepository);
    }

    protected function handle(array $data): mixed
    {
        return null;
    }

    public function listByWarehouse(
        int $tenantId,
        int $warehouseId,
        array $filters = [],
        int $perPage = 15,
        int $page = 1,
        ?string $sort = null,
    ): mixed {
        return $this->stockMovementRepository->paginateByWarehouse($tenantId, $warehouseId, $filters, $perPage, $page, $sort);
    }
}
