<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Domain\Entities\StockMovement;

interface StockMovementServiceInterface
{
    public function record(array $data): StockMovement;

    public function getByProduct(int $productId, int $tenantId): array;

    public function getByLocation(int $locationId, int $tenantId): array;

    public function getByBatch(string $batchNumber, int $tenantId): array;
}
