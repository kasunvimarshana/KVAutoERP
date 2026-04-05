<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class Product
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $sku,
        private readonly ?string $barcode,
        private readonly string $type,
        private readonly ?int $categoryId,
        private readonly ?string $description,
        private readonly string $unit,
        private readonly float $costPrice,
        private readonly float $sellingPrice,
        private readonly ?int $taxGroupId,
        private readonly bool $trackInventory,
        private readonly bool $isActive,
        private readonly ?float $weight,
        private readonly ?array $dimensions,
        private readonly array $images,
        private readonly array $tags,
        private readonly array $metadata,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getCostPrice(): float
    {
        return $this->costPrice;
    }

    public function getSellingPrice(): float
    {
        return $this->sellingPrice;
    }

    public function getTaxGroupId(): ?int
    {
        return $this->taxGroupId;
    }

    public function tracksInventory(): bool
    {
        return $this->trackInventory;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function isVariable(): bool
    {
        return $this->type === 'variable';
    }
}
