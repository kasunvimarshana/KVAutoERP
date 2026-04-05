<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\StockItem;

interface StockItemRepositoryInterface
{
    public function findById(int $id): ?StockItem;

    public function findByProduct(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
    ): ?StockItem;

    public function findByWarehouse(int $tenantId, int $warehouseId): array;

    public function updateQuantity(int $id, float $qty): ?StockItem;

    public function updateReserved(int $id, float $qty): ?StockItem;

    public function upsert(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $qty,
        float $cost,
    ): StockItem;

    public function create(array $data): StockItem;

    public function update(int $id, array $data): ?StockItem;

    public function all(int $tenantId): array;
}
