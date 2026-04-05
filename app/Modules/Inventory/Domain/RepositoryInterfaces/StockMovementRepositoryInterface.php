<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\StockMovement;

interface StockMovementRepositoryInterface
{
    public function findById(int $id): ?StockMovement;

    public function findByProduct(int $tenantId, int $productId): Collection;

    public function findByType(int $tenantId, string $type): Collection;

    public function findByDateRange(int $tenantId, string $from, string $to): Collection;

    public function record(array $data): StockMovement;
}
