<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateProductData extends BaseDto
{
    public ?string $name = null;
    public ?string $sku = null;
    public ?string $barcode = null;
    public ?string $type = null;
    public ?string $status = null;
    public ?int $categoryId = null;
    public ?string $description = null;
    public ?string $shortDescription = null;
    public ?float $weight = null;
    public ?array $dimensions = null;
    public ?array $images = null;
    public ?array $tags = null;
    public ?bool $isTaxable = null;
    public ?string $taxClass = null;
    public ?bool $hasSerial = null;
    public ?bool $hasBatch = null;
    public ?bool $hasLot = null;
    public ?bool $isSerialized = null;
    public ?int $updatedBy = null;
}
