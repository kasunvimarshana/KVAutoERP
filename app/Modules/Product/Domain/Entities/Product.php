<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class Product
{
    public function __construct(
        public ?int $id,
        public int $tenantId,
        public ?int $categoryId,
        public string $name,
        public string $sku,
        public ?string $barcode,
        public string $type,
        public ?string $description,
        public ?string $image,
        public ?int $baseUomId,
        public string $status,
        public bool $isTrackable,
        public bool $isSerialized,
        public bool $isBatchTracked,
        public bool $hasExpiry,
        public ?string $weight,
        public ?string $weightUnit,
        public ?string $length,
        public ?string $width,
        public ?string $height,
        public ?string $dimensionUnit,
        public ?array $attributes,
        public ?array $metadata,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}
}
