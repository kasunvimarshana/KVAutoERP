<?php declare(strict_types=1);
namespace Modules\Inventory\Application\Services;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Domain\Entities\StockItem;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
class InventoryManagerService implements InventoryManagerServiceInterface {
    public function __construct(
        private readonly StockItemRepositoryInterface $stockRepo,
        private readonly StockMovementRepositoryInterface $movementRepo,
        private readonly AddValuationLayerService $addLayerService,
        private readonly ConsumeValuationLayersService $consumeService,
    ) {}
    public function receive(array $data): StockMovement {
        $movement = new StockMovement(null,$data['tenant_id'],$data['product_id'],$data['variant_id']??null,$data['warehouse_id'],$data['location_id']??null,'receive',(float)$data['quantity'],(float)($data['unit_cost']??0.0),$data['reference']??'RECV',$data['batch_number']??null,$data['lot_number']??null,$data['serial_number']??null,isset($data['expiry_date'])?new \DateTimeImmutable($data['expiry_date']):null,new \DateTimeImmutable(),$data['notes']??null);
        $saved = $this->movementRepo->save($movement);
        $existing = $this->stockRepo->findByProductAndWarehouse($data['tenant_id'],$data['product_id'],$data['warehouse_id']);
        if ($existing) {
            $newQty = $existing->getQuantity() + (float)$data['quantity'];
            $item = new StockItem($existing->getId(),$existing->getTenantId(),$existing->getProductId(),$existing->getVariantId(),$existing->getWarehouseId(),$existing->getLocationId(),$newQty,$existing->getReservedQuantity(),$newQty - $existing->getReservedQuantity(),$existing->getUnit());
        } else {
            $qty = (float)$data['quantity'];
            $item = new StockItem(null,$data['tenant_id'],$data['product_id'],$data['variant_id']??null,$data['warehouse_id'],$data['location_id']??null,$qty,0.0,$qty,$data['unit']??'unit');
        }
        $this->stockRepo->save($item);
        $this->addLayerService->add(['tenant_id'=>$data['tenant_id'],'product_id'=>$data['product_id'],'variant_id'=>$data['variant_id']??null,'warehouse_id'=>$data['warehouse_id'],'quantity'=>$data['quantity'],'unit_cost'=>$data['unit_cost']??0.0,'batch_number'=>$data['batch_number']??null,'lot_number'=>$data['lot_number']??null,'expiry_date'=>$data['expiry_date']??null]);
        return $saved;
    }
    public function issue(array $data): StockMovement {
        $qty = (float)$data['quantity'];
        $method = $data['method'] ?? 'fifo';
        $avgCost = $this->consumeService->consume($data['tenant_id'],$data['product_id'],$data['warehouse_id'],$qty,$method);
        $movement = new StockMovement(null,$data['tenant_id'],$data['product_id'],$data['variant_id']??null,$data['warehouse_id'],$data['location_id']??null,'issue',-$qty,$avgCost,$data['reference']??'ISSUE',$data['batch_number']??null,$data['lot_number']??null,$data['serial_number']??null,null,new \DateTimeImmutable(),$data['notes']??null);
        $saved = $this->movementRepo->save($movement);
        $existing = $this->stockRepo->findByProductAndWarehouse($data['tenant_id'],$data['product_id'],$data['warehouse_id']);
        if ($existing) {
            $newQty = max(0.0, $existing->getQuantity() - $qty);
            $item = new StockItem($existing->getId(),$existing->getTenantId(),$existing->getProductId(),$existing->getVariantId(),$existing->getWarehouseId(),$existing->getLocationId(),$newQty,$existing->getReservedQuantity(),max(0.0,$newQty - $existing->getReservedQuantity()),$existing->getUnit());
            $this->stockRepo->save($item);
        }
        return $saved;
    }
}
