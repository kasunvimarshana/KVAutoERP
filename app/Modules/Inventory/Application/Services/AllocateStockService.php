<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AllocateStockServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchLotRepositoryInterface;

class AllocateStockService implements AllocateStockServiceInterface
{
    public function __construct(
        private readonly BatchLotRepositoryInterface $batchRepo,
    ) {}

    public function allocate(
        int $tenantId,
        int $productId,
        ?int $variantId,
        float $quantity,
        string $method,
    ): array {
        $batches = $this->batchRepo->findByProduct($productId, $tenantId);

        $batches = array_values(array_filter(
            $batches,
            static fn ($b) => $b->status === 'active'
                && $b->remainingQuantity > 0.0
                && ($variantId === null || $b->variantId === $variantId),
        ));

        usort($batches, match ($method) {
            'lifo'  => static fn ($a, $b) => $b->id <=> $a->id,
            'fefo'  => static function ($a, $b): int {
                $aTs = $a->expiryDate?->getTimestamp() ?? PHP_INT_MAX;
                $bTs = $b->expiryDate?->getTimestamp() ?? PHP_INT_MAX;
                return $aTs <=> $bTs;
            },
            default => static fn ($a, $b) => $a->id <=> $b->id, // fifo
        });

        $remaining   = $quantity;
        $allocations = [];

        foreach ($batches as $batch) {
            if ($remaining <= 0.0) {
                break;
            }

            $alloc         = min($batch->remainingQuantity, $remaining);
            $allocations[] = [
                'batchLotId' => $batch->id,
                'quantity'   => $alloc,
                'locationId' => $batch->locationId,
                'expiryDate' => $batch->expiryDate?->format('Y-m-d'),
            ];
            $remaining -= $alloc;
        }

        return [
            'allocations'     => $allocations,
            'total_allocated' => $quantity - $remaining,
        ];
    }
}
