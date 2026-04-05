<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AllocateStockServiceInterface;
use Modules\Inventory\Domain\Entities\Batch;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchRepositoryInterface;

class AllocateStockService implements AllocateStockServiceInterface
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
    ) {}

    public function allocate(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        float $quantity,
        string $strategy,
    ): array {
        $batches = $this->batchRepository->findByProduct($tenantId, $productId, $variantId, 'active');

        // Filter to the target warehouse only
        $batches = array_values(
            array_filter($batches, fn (Batch $b) => $b->getWarehouseId() === $warehouseId && $b->getQuantity() > 0),
        );

        $batches = $this->sortBatches($batches, $strategy);

        $allocations = [];
        $remaining   = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take      = min($batch->getQuantity(), $remaining);
            $remaining -= $take;

            $allocations[] = [
                'batchId'    => $batch->getId(),
                'quantity'   => $take,
                'locationId' => $batch->getLocationId(),
            ];
        }

        return $allocations;
    }

    /** @param Batch[] $batches */
    private function sortBatches(array $batches, string $strategy): array
    {
        usort($batches, function (Batch $a, Batch $b) use ($strategy): int {
            return match (strtoupper($strategy)) {
                'FEFO' => $this->compareFefo($a, $b),
                'LIFO' => $b->getCreatedAt() <=> $a->getCreatedAt(),
                default => $a->getCreatedAt() <=> $b->getCreatedAt(), // FIFO
            };
        });

        return $batches;
    }

    private function compareFefo(Batch $a, Batch $b): int
    {
        $expiryA = $a->getExpiryDate();
        $expiryB = $b->getExpiryDate();

        if ($expiryA === null && $expiryB === null) {
            return $a->getCreatedAt() <=> $b->getCreatedAt();
        }

        if ($expiryA === null) {
            return 1;
        }

        if ($expiryB === null) {
            return -1;
        }

        return $expiryA <=> $expiryB;
    }
}
