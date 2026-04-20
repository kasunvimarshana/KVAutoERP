<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Warehouse\Application\Contracts\FindWarehouseStockLevelServiceInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseStockMovementRepositoryInterface;

class FindWarehouseStockLevelService extends BaseService implements FindWarehouseStockLevelServiceInterface
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

    public function listByWarehouse(int $tenantId, int $warehouseId, int $perPage = 15, int $page = 1): mixed
    {
        return $this->stockMovementRepository->paginateStockLevelsByWarehouse($tenantId, $warehouseId, $perPage, $page);
    }
}
