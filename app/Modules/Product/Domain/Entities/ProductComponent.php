<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class ProductComponent
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $productId,
        private readonly int $componentProductId,
        private readonly ?int $componentVariantId,
        private readonly float $quantity,
        private readonly string $unit,
        private readonly ?string $notes,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getComponentProductId(): int
    {
        return $this->componentProductId;
    }

    public function getComponentVariantId(): ?int
    {
        return $this->componentVariantId;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }
}
