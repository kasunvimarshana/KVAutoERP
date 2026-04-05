<?php declare(strict_types=1);
namespace Modules\Inventory\Application\Services;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
class ConsumeValuationLayersService {
    public function __construct(private readonly ValuationLayerRepositoryInterface $repo) {}
    /**
     * @param string $method fifo|lifo|average
     * @return float weighted average cost per unit consumed
     */
    public function consume(int $tenantId, int $productId, int $warehouseId, float $quantity, string $method = 'fifo'): float {
        $layers = match($method) {
            'lifo' => $this->repo->findActiveByProductDesc($tenantId, $productId, $warehouseId),
            'fefo' => $this->repo->findActiveByExpiry($tenantId, $productId, $warehouseId),
            default => $this->repo->findActiveByProduct($tenantId, $productId, $warehouseId),
        };
        $remaining = $quantity;
        $totalCost = 0.0;
        foreach ($layers as $layer) {
            if (abs($remaining) < PHP_FLOAT_EPSILON) break;
            $consume = min($remaining, $layer->getRemainingQuantity());
            $totalCost += $consume * $layer->getUnitCost();
            $remaining -= $consume;
            $this->repo->save($layer->withRemainingQuantity($layer->getRemainingQuantity() - $consume));
        }
        if ($remaining > PHP_FLOAT_EPSILON) throw new \DomainException("Insufficient stock: {$remaining} units unavailable");
        return abs($quantity) < PHP_FLOAT_EPSILON ? 0.0 : $totalCost / $quantity;
    }
}
