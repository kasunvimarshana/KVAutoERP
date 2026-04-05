<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\ConsumeValuationLayersServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;

class ConsumeValuationLayersService implements ConsumeValuationLayersServiceInterface
{
    public function __construct(
        private readonly ValuationLayerRepositoryInterface $repo,
    ) {}

    public function consume(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $locationId,
        float $quantity,
        string $method,
    ): array {
        $layers = $this->repo->getLayersForConsumption(
            $productId,
            $variantId,
            $locationId,
            $tenantId,
            $method,
        );

        $remaining      = $quantity;
        $totalCost      = 0.0;
        $layersConsumed = 0;

        foreach ($layers as $layer) {
            if ($remaining <= 0.0) {
                break;
            }

            $consume    = min($layer->remainingQuantity, $remaining);
            $totalCost  += $consume * $layer->unitCost;
            $remaining  -= $consume;
            $newRemaining = $layer->remainingQuantity - $consume;

            $this->repo->update($layer->id, ['remaining_quantity' => $newRemaining]);
            ++$layersConsumed;
        }

        $consumed = $quantity - $remaining;

        return [
            'layers_consumed'   => $layersConsumed,
            'weighted_avg_cost' => $consumed > 0.0 ? ($totalCost / $consumed) : 0.0,
            'total_cost'        => $totalCost,
        ];
    }
}
