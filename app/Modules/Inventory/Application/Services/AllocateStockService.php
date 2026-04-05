<?php declare(strict_types=1);
namespace Modules\Inventory\Application\Services;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
class AllocateStockService {
    public function __construct(private readonly ValuationLayerRepositoryInterface $repo) {}
    /**
     * Returns allocation plan: [['layer_id'=>int,'batch'=>?string,'lot'=>?string,'expiry'=>?string,'qty'=>float]]
     */
    public function allocate(int $tenantId, int $productId, int $warehouseId, float $quantity, string $strategy = 'fefo'): array {
        $layers = match($strategy) {
            'lifo' => $this->repo->findActiveByProductDesc($tenantId, $productId, $warehouseId),
            'fifo' => $this->repo->findActiveByProduct($tenantId, $productId, $warehouseId),
            default => $this->repo->findActiveByExpiry($tenantId, $productId, $warehouseId),
        };
        $remaining = $quantity;
        $plan = [];
        foreach ($layers as $layer) {
            if (abs($remaining) < PHP_FLOAT_EPSILON) break;
            $take = min($remaining, $layer->getRemainingQuantity());
            $plan[] = ['layer_id'=>$layer->getId(),'batch'=>$layer->getBatchNumber(),'lot'=>$layer->getLotNumber(),'expiry'=>$layer->getExpiryDate()?->format('Y-m-d'),'qty'=>$take];
            $remaining -= $take;
        }
        if ($remaining > PHP_FLOAT_EPSILON) throw new \DomainException("Cannot allocate {$quantity} units; insufficient stock");
        return $plan;
    }
}
