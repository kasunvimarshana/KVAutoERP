<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\ConsumeValuationLayersServiceInterface;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;

final class ConsumeValuationLayersService implements ConsumeValuationLayersServiceInterface
{
    public function __construct(
        private readonly ValuationLayerRepositoryInterface $valuationLayerRepository,
    ) {}

    public function consume(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        float $qty,
        string $method,
    ): float {
        $layers = $this->valuationLayerRepository->getActiveByMethod(
            $tenantId,
            $productId,
            $variantId,
            $warehouseId,
            $method,
        );

        $remaining     = $qty;
        $totalCost     = 0.0;
        $totalConsumed = 0.0;

        foreach ($layers as $layer) {
            if ($remaining <= 0.0) {
                break;
            }

            /** @var ValuationLayer $layer */
            $toConsume = min($layer->quantityRemaining, $remaining);

            $this->valuationLayerRepository->consumeLayer($layer->id, $toConsume);

            $totalCost     += $toConsume * $layer->costPerUnit;
            $totalConsumed += $toConsume;
            $remaining     -= $toConsume;
        }

        if ($remaining > 0.0) {
            throw new \RuntimeException(
                sprintf(
                    'Insufficient stock to consume %.6f units for product %d (warehouse %d).',
                    $qty,
                    $productId,
                    $warehouseId,
                )
            );
        }

        return $totalConsumed > 0.0 ? $totalCost / $totalConsumed : 0.0;
    }
}
