<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class Product
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public string $name,
        public string $sku,
        public ?string $barcode,
        public string $type,
        public string $status,
        public ?int $categoryId,
        public ?string $description,
        public ?string $shortDescription,
        public ?float $weight,
        public ?array $dimensions,
        public ?array $images,
        public ?array $tags,
        public bool $isTaxable,
        public ?string $taxClass,
        public bool $hasSerial,
        public bool $hasBatch,
        public bool $hasLot,
        public bool $isSerialized,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
    ) {}
}
