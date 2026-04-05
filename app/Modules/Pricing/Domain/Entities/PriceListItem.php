<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Entities;

class PriceListItem
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $priceListId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly string $priceType,
        private readonly float $price,
        private readonly float $minQuantity,
        private readonly ?float $maxQuantity,
        private readonly bool $isActive,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPriceListId(): int
    {
        return $this->priceListId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getVariantId(): ?int
    {
        return $this->variantId;
    }

    public function getPriceType(): string
    {
        return $this->priceType;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getMinQuantity(): float
    {
        return $this->minQuantity;
    }

    public function getMaxQuantity(): ?float
    {
        return $this->maxQuantity;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function calculatePrice(float $basePrice, float $quantity): float
    {
        if ($quantity < $this->minQuantity) {
            return $basePrice;
        }

        if ($this->maxQuantity !== null && $quantity > $this->maxQuantity) {
            return $basePrice;
        }

        if ($this->priceType === 'fixed') {
            return $this->price;
        }

        // percentage: discount from base price
        return $basePrice * (1 - $this->price / 100);
    }
}
