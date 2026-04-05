<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\Entities;
class StockItem {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly int $warehouseId,
        private readonly ?int $locationId,
        private readonly float $quantity,
        private readonly float $reservedQuantity,
        private readonly float $availableQuantity,
        private readonly string $unit,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function getLocationId(): ?int { return $this->locationId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getReservedQuantity(): float { return $this->reservedQuantity; }
    public function getAvailableQuantity(): float { return $this->availableQuantity; }
    public function getUnit(): string { return $this->unit; }
}
