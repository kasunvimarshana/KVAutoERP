<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\StockItem;

interface StockItemRepositoryInterface
{
    public function findById(int $id): ?StockItem;

    public function findByProduct(int $tenantId, int $productId, ?int $variantId = null): Collection;

    public function findByLocation(int $locationId): Collection;

    public function findByWarehouse(int $warehouseId): Collection;

    public function findPosition(int $productId, ?int $variantId, int $warehouseId, ?int $locationId): ?StockItem;

    public function updateQuantity(int $id, array $data): ?StockItem;

    public function upsertPosition(array $data): StockItem;

    public function reserve(int $id, float $quantity): bool;

    public function release(int $id, float $quantity): bool;
}
