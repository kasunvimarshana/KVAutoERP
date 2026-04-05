<?php

declare(strict_types=1);

namespace Modules\Purchase\Domain\Entities;

class PurchaseReceipt
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $purchaseOrderId,
        private readonly string $referenceNo,
        private readonly \DateTimeInterface $receiptDate,
        private readonly int $warehouseId,
        private readonly ?int $locationId,
        private readonly ?string $notes,
        private readonly ?int $createdBy,
        private readonly ?\DateTimeInterface $createdAt,
        private readonly ?\DateTimeInterface $updatedAt,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getPurchaseOrderId(): int { return $this->purchaseOrderId; }
    public function getReferenceNo(): string { return $this->referenceNo; }
    public function getReceiptDate(): \DateTimeInterface { return $this->receiptDate; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function getLocationId(): ?int { return $this->locationId; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
}
