<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AllocateStockServiceInterface;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;

final class AllocateStockService implements AllocateStockServiceInterface
{
    public function __construct(
        private readonly ValuationLayerRepositoryInterface $valuationLayerRepository,
    ) {}

    public function allocate(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        float $qty,
        string $strategy = 'FEFO',
    ): array {
        $method = match (strtoupper($strategy)) {
            'LIFO'       => 'lifo',
            'FIFO'       => 'fifo',
            'FEFO'       => 'fifo',
            default      => 'fifo',
        };

        $layers = $this->valuationLayerRepository->getActiveByMethod(
            $tenantId,
            $productId,
            $variantId,
            $warehouseId,
            $method,
        );

        // Apply FEFO ordering: sort by expiry_date ASC (nulls last), then by received_at ASC
        if (strtoupper($strategy) === 'FEFO') {
            $layers = $layers->sortBy(function (ValuationLayer $layer): string {
                $expiry = $layer->expiryDate?->format('Y-m-d') ?? '9999-12-31';

                return $expiry . '-' . $layer->receivedAt->format('Y-m-d');
            })->values();
        }

        $remaining   = $qty;
        $allocations = [];

        foreach ($layers as $layer) {
            if ($remaining <= 0.0) {
                break;
            }

            /** @var ValuationLayer $layer */
            $allocated = min($layer->quantityRemaining, $remaining);

            $allocations[] = [
                'layer_id'     => $layer->id,
                'batch_number' => $layer->batchNumber,
                'lot_number'   => $layer->lotNumber,
                'serial_number' => $layer->serialNumber,
                'expiry_date'  => $layer->expiryDate?->format('Y-m-d'),
                'quantity'     => $allocated,
                'cost_per_unit' => $layer->costPerUnit,
            ];

            $remaining -= $allocated;
        }

        if ($remaining > 0.0) {
            throw new \RuntimeException(
                sprintf(
                    'Insufficient stock to allocate %.6f units for product %d (warehouse %d).',
                    $qty,
                    $productId,
                    $warehouseId,
                )
            );
        }

        return $allocations;
    }
}
