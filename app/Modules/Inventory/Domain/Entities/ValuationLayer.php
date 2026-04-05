<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\Entities;
/** A costing layer for FIFO/LIFO/Average valuation */
class ValuationLayer {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly int $warehouseId,
        private readonly float $quantity,
        private readonly float $remainingQuantity,
        private readonly float $unitCost,
        private readonly \DateTimeInterface $receivedAt,
        private readonly ?string $batchNumber,
        private readonly ?string $lotNumber,
        private readonly ?\DateTimeInterface $expiryDate,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getRemainingQuantity(): float { return $this->remainingQuantity; }
    public function getUnitCost(): float { return $this->unitCost; }
    public function getReceivedAt(): \DateTimeInterface { return $this->receivedAt; }
    public function getBatchNumber(): ?string { return $this->batchNumber; }
    public function getLotNumber(): ?string { return $this->lotNumber; }
    public function getExpiryDate(): ?\DateTimeInterface { return $this->expiryDate; }
    public function isEmpty(): bool { return abs($this->remainingQuantity) < PHP_FLOAT_EPSILON; }
    public function withRemainingQuantity(float $qty): self {
        return new self($this->id,$this->tenantId,$this->productId,$this->variantId,$this->warehouseId,$this->quantity,$qty,$this->unitCost,$this->receivedAt,$this->batchNumber,$this->lotNumber,$this->expiryDate);
    }
}
