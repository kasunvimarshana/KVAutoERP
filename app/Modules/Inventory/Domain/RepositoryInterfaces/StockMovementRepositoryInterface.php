<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\StockMovement;

interface StockMovementRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?StockMovement;

    public function findByProduct(int $productId, int $tenantId): array;

    public function findByLocation(int $locationId, int $tenantId): array;

    public function findByBatch(string $batchNumber, int $tenantId): array;

    public function allByTenant(int $tenantId): array;

    public function create(array $data): StockMovement;
}
