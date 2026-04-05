<?php declare(strict_types=1);
namespace Modules\Asset\Domain\Entities;

class FixedAsset
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $code,
        private readonly string $name,
        private readonly string $category,
        private readonly float $purchaseCost,
        private readonly \DateTimeInterface $purchaseDate,
        private readonly float $residualValue,
        private readonly int $usefulLifeMonths,
        private readonly string $depreciationMethod,
        private readonly string $status,
        private readonly ?int $warehouseId,
        private readonly ?string $notes,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getCategory(): string { return $this->category; }
    public function getPurchaseCost(): float { return $this->purchaseCost; }
    public function getPurchaseDate(): \DateTimeInterface { return $this->purchaseDate; }
    public function getResidualValue(): float { return $this->residualValue; }
    public function getUsefulLifeMonths(): int { return $this->usefulLifeMonths; }
    public function getDepreciationMethod(): string { return $this->depreciationMethod; }
    public function getStatus(): string { return $this->status; }
    public function getWarehouseId(): ?int { return $this->warehouseId; }
    public function getNotes(): ?string { return $this->notes; }

    public function getDepreciableAmount(): float
    {
        return $this->purchaseCost - $this->residualValue;
    }

    public function getMonthlyDepreciation(): float
    {
        if ($this->usefulLifeMonths <= 0) {
            return 0.0;
        }
        return $this->getDepreciableAmount() / $this->usefulLifeMonths;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
