<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\StockMovementServiceInterface;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;

class StockMovementService implements StockMovementServiceInterface
{
    public function __construct(
        private readonly StockMovementRepositoryInterface $repo,
    ) {}

    public function record(array $data): StockMovement
    {
        return $this->repo->create($data);
    }

    public function getByProduct(int $productId, int $tenantId): array
    {
        return $this->repo->findByProduct($productId, $tenantId);
    }

    public function getByLocation(int $locationId, int $tenantId): array
    {
        return $this->repo->findByLocation($locationId, $tenantId);
    }

    public function getByBatch(string $batchNumber, int $tenantId): array
    {
        return $this->repo->findByBatch($batchNumber, $tenantId);
    }
}
