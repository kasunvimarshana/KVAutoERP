<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class ProductVariant
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $productId,
        private readonly string $name,
        private readonly string $sku,
        private readonly ?string $barcode,
        private readonly array $attributes,
        private readonly ?float $costPrice,
        private readonly ?float $sellingPrice,
        private readonly float $stockQty,
        private readonly bool $isActive,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getCostPrice(): ?float
    {
        return $this->costPrice;
    }

    public function getSellingPrice(): ?float
    {
        return $this->sellingPrice;
    }

    public function getStockQty(): float
    {
        return $this->stockQty;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getEffectiveCostPrice(float $productCost): float
    {
        return $this->costPrice ?? $productCost;
    }

    public function getEffectiveSellingPrice(float $productPrice): float
    {
        return $this->sellingPrice ?? $productPrice;
    }
}
