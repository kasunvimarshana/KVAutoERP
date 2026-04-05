<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\InventoryAdjustment;

interface InventoryAdjustmentRepositoryInterface
{
    public function findById(int $id): ?InventoryAdjustment;

    public function create(array $data): InventoryAdjustment;

    public function update(int $id, array $data): ?InventoryAdjustment;

    public function findByWarehouse(int $tenantId, int $warehouseId): array;
}
