<?php declare(strict_types=1);
namespace Modules\Inventory\Application\Services;
use Modules\Inventory\Domain\Entities\CycleCountLine;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
class ReconcileInventoryService {
    public function __construct(
        private readonly CycleCountRepositoryInterface $cycleCountRepo,
        private readonly StockMovementRepositoryInterface $movementRepo,
    ) {}
    public function reconcile(int $cycleCountId, int $warehouseId, int $tenantId): array {
        $lines = $this->cycleCountRepo->findLinesByCount($cycleCountId);
        $adjustments = [];
        foreach ($lines as $line) {
            if ($line->getCountedQuantity() === null) continue;
            $variance = $line->getCountedQuantity() - $line->getSystemQuantity();
            if (abs($variance) < PHP_FLOAT_EPSILON) continue;
            $movement = new StockMovement(null,$tenantId,$line->getProductId(),null,$warehouseId,$line->getLocationId(),'adjustment',$variance,0.0,"CC-{$cycleCountId}",null,null,null,null,new \DateTimeImmutable(),"Cycle count reconciliation");
            $saved = $this->movementRepo->save($movement);
            $adjustments[] = $saved;
        }
        return $adjustments;
    }
}
