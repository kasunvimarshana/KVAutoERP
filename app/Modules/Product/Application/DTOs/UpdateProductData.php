<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateProductData extends BaseDto
{
    public function __construct(
        public ?string $name = null,
        public ?int $categoryId = null,
        public ?string $barcode = null,
        public ?string $type = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?int $baseUomId = null,
        public ?string $status = null,
        public ?bool $isTrackable = null,
        public ?bool $isSerialized = null,
        public ?bool $isBatchTracked = null,
        public ?bool $hasExpiry = null,
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
