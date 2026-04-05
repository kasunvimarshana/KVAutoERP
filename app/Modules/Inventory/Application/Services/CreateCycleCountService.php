<?php declare(strict_types=1);
namespace Modules\Inventory\Application\Services;
use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
class CreateCycleCountService implements CreateCycleCountServiceInterface {
    public function __construct(
        private readonly CycleCountRepositoryInterface $repo,
        private readonly StockItemRepositoryInterface $stockRepo,
    ) {}
    public function create(array $data): CycleCount {
        $cycleCount = new CycleCount(null,$data['tenant_id'],$data['warehouse_id'],'pending',$data['reference'],isset($data['scheduled_at'])?new \DateTimeImmutable($data['scheduled_at']):null,null);
        $saved = $this->repo->save($cycleCount);
        $items = $this->stockRepo->findByWarehouse($data['tenant_id'],$data['warehouse_id']);
        foreach ($items as $item) {
            $line = new CycleCountLine(null,$saved->getId(),$item->getProductId(),$item->getLocationId(),$item->getQuantity(),null,null);
            $this->repo->saveLine($line);
        }
        return $saved;
    }
}
