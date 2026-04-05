<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\StockAdjustment;
use Modules\Inventory\Domain\Entities\StockAdjustmentLine;

interface StockAdjustmentRepositoryInterface
{
    public function findById(int $id): ?StockAdjustment;

    public function findByTenant(int $tenantId): Collection;

    public function findByWarehouse(int $warehouseId): Collection;

    public function create(array $data): StockAdjustment;

    public function update(int $id, array $data): ?StockAdjustment;

    public function addLine(int $adjustmentId, array $lineData): StockAdjustmentLine;

    public function getLines(int $adjustmentId): Collection;
}
