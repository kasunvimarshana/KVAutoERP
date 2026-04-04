<?php
namespace Modules\Inventory\Domain\Entities;

use Modules\Core\Domain\Entities\BaseEntity;

class InventoryValuationLayer extends BaseEntity
{
    public function __construct(
        ?int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly int $warehouseId,
        public readonly string $valuationMethod,
        public float $quantity,
        public float $unitCost,
        public float $totalCost,
        public readonly ?int $batchId = null,
        public readonly ?\DateTimeImmutable $receiptDate = null,
        public readonly ?int $referenceId = null,
        public readonly ?string $referenceType = null,
    ) {
        parent::__construct($id);
    }
}
