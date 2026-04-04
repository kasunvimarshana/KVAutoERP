<?php
namespace Modules\Inventory\Domain\Entities;

use Modules\Core\Domain\Entities\BaseEntity;

class InventoryLevel extends BaseEntity
{
    public function __construct(
        ?int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly int $warehouseId,
        public readonly int $locationId,
        public float $quantityOnHand,
        public float $quantityReserved,
        public float $quantityAvailable,
        public float $quantityOnOrder,
        public readonly ?int $batchId = null,
        public readonly ?int $lotId = null,
        public readonly ?int $serialId = null,
        public readonly ?string $stockStatus = 'available',
    ) {
        parent::__construct($id);
    }

    public function reserve(float $qty): void
    {
        if ($qty > $this->quantityAvailable) {
            throw new \DomainException("Insufficient available stock to reserve.");
        }
        $this->quantityReserved += $qty;
        $this->quantityAvailable -= $qty;
    }

    public function release(float $qty): void
    {
        $qty = min($qty, $this->quantityReserved);
        $this->quantityReserved -= $qty;
        $this->quantityAvailable += $qty;
    }

    public function adjust(float $newQtyOnHand): void
    {
        $this->quantityOnHand    = $newQtyOnHand;
        $this->quantityAvailable = $newQtyOnHand - $this->quantityReserved;
    }
}
