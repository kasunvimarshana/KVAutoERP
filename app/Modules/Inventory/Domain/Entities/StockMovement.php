<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\Entities;
class StockMovement {
    public const TYPES = ['receive','issue','transfer','adjustment','return','cycle_count'];
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly int $warehouseId,
        private readonly ?int $locationId,
        private readonly string $type,
        private readonly float $quantity,
        private readonly float $unitCost,
        private readonly string $reference,
        private readonly ?string $batchNumber,
        private readonly ?string $lotNumber,
        private readonly ?string $serialNumber,
        private readonly ?\DateTimeInterface $expiryDate,
        private readonly \DateTimeInterface $movedAt,
        private readonly ?string $notes,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function getLocationId(): ?int { return $this->locationId; }
    public function getType(): string { return $this->type; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitCost(): float { return $this->unitCost; }
    public function getReference(): string { return $this->reference; }
    public function getBatchNumber(): ?string { return $this->batchNumber; }
    public function getLotNumber(): ?string { return $this->lotNumber; }
    public function getSerialNumber(): ?string { return $this->serialNumber; }
    public function getExpiryDate(): ?\DateTimeInterface { return $this->expiryDate; }
    public function getMovedAt(): \DateTimeInterface { return $this->movedAt; }
    public function getNotes(): ?string { return $this->notes; }
}
