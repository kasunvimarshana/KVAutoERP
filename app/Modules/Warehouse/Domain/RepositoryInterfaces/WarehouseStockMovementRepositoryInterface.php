<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\RepositoryInterfaces;

use Modules\Warehouse\Domain\Entities\StockMovement;

interface WarehouseStockMovementRepositoryInterface
{
    public function recordMovement(StockMovement $movement): StockMovement;

    public function adjustStockLevel(StockMovement $movement): void;

    public function paginateByWarehouse(
        int $tenantId,
        int $warehouseId,
        array $filters,
        int $perPage,
        int $page,
        ?string $sort = null,
    ): mixed;

    public function paginateStockLevelsByWarehouse(int $tenantId, int $warehouseId, int $perPage, int $page): mixed;

    public function movementBelongsToWarehouse(int $tenantId, int $warehouseId, int $locationId): bool;

    public function stockLevelLocationBelongsToWarehouse(int $tenantId, int $warehouseId, int $locationId): bool;
}
