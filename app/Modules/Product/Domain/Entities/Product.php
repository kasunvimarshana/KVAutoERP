<?php declare(strict_types=1);
namespace Modules\Product\Domain\Entities;
class Product {
    public const TYPES = ['physical','service','digital','combo','variable'];
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $sku,
        private readonly string $name,
        private readonly string $type, // physical|service|digital|combo|variable
        private readonly ?int $categoryId,
        private readonly float $costPrice,
        private readonly float $salePrice,
        private readonly string $currency,
        private readonly ?string $description,
        private readonly bool $isActive,
        private readonly bool $isTaxable,
        private readonly ?int $taxGroupId,
        private readonly ?string $barcode,
        private readonly ?string $unit,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getSku(): string { return $this->sku; }
    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function getCostPrice(): float { return $this->costPrice; }
    public function getSalePrice(): float { return $this->salePrice; }
    public function getCurrency(): string { return $this->currency; }
    public function getDescription(): ?string { return $this->description; }
    public function isActive(): bool { return $this->isActive; }
    public function isTaxable(): bool { return $this->isTaxable; }
    public function getTaxGroupId(): ?int { return $this->taxGroupId; }
    public function getBarcode(): ?string { return $this->barcode; }
    public function getUnit(): ?string { return $this->unit; }
    public function isInventoried(): bool { return in_array($this->type, ['physical','combo']); }
}
