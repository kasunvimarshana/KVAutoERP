<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateProductData extends BaseDto
{
    public function __construct(
        public int $tenantId,
        public string $name,
        public string $sku,
        public ?int $categoryId = null,
        public ?string $barcode = null,
        public string $type = 'physical',
        public ?string $description = null,
        public ?string $image = null,
        public ?int $baseUomId = null,
        public string $status = 'active',
        public bool $isTrackable = true,
        public bool $isSerialized = false,
        public bool $isBatchTracked = false,
        public bool $hasExpiry = false,
        public ?string $weight = null,
        public ?string $weightUnit = null,
        public ?string $length = null,
        public ?string $width = null,
        public ?string $height = null,
        public ?string $dimensionUnit = null,
        public ?array $attributes = null,
        public ?array $metadata = null,
    ) {}
}
