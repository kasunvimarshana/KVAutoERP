<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\ConsumeValuationLayersServiceInterface;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;

class ConsumeValuationLayersService implements ConsumeValuationLayersServiceInterface
{
    public function __construct(
        private readonly ValuationLayerRepositoryInterface $repository,
    ) {}

    public function consume(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        string $method,
        float $quantity,
    ): float {
        $layers = $this->repository->findAvailable($tenantId, $productId, $variantId, $method);

        if (empty($layers)) {
            return 0.0;
        }

        return match ($method) {
            'fifo'    => $this->consumeOrdered($layers, $quantity),
            'lifo'    => $this->consumeOrdered($layers, $quantity),
            'average' => $this->consumeAverage($layers, $quantity),
            default   => $this->consumeOrdered($layers, $quantity),
        };
    }

    /** @param ValuationLayer[] $layers */
    private function consumeOrdered(array $layers, float $remaining): float
    {
        $totalCost = 0.0;
        $totalConsumed = 0.0;

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }

            $take     = min($layer->getQuantity(), $remaining);
            $totalCost     += $take * $layer->getUnitCost();
            $totalConsumed += $take;
            $remaining     -= $take;

            $newQty = $layer->getQuantity() - $take;
            $this->repository->update($layer->getId(), ['quantity' => $newQty]);
        }

        return $totalConsumed > 0 ? $totalCost / $totalConsumed : 0.0;
    }

    /** @param ValuationLayer[] $layers */
    private function consumeAverage(array $layers, float $quantity): float
    {
        $totalQty  = 0.0;
        $totalCost = 0.0;

        foreach ($layers as $layer) {
            $totalQty  += $layer->getQuantity();
            $totalCost += $layer->getQuantity() * $layer->getUnitCost();
        }

        if ($totalQty <= 0) {
            return 0.0;
        }

        $avgCost = $totalCost / $totalQty;

        // Reduce each layer proportionally
        foreach ($layers as $layer) {
            $proportion = $layer->getQuantity() / $totalQty;
            $take       = $proportion * $quantity;
            $newQty     = max(0.0, $layer->getQuantity() - $take);
            $this->repository->update($layer->getId(), ['quantity' => $newQty]);
        }

        return $avgCost;
    }
}
