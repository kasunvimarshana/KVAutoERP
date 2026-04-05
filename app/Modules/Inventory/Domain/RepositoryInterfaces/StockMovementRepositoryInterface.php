<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\StockMovement;

interface StockMovementRepositoryInterface
{
    public function findById(int $id): ?StockMovement;

    public function findByProduct(int $tenantId, int $productId): array;

    public function findByWarehouse(int $tenantId, int $warehouseId): array;

    public function findByReference(string $referenceType, int $referenceId): array;

    public function create(array $data): StockMovement;
}
