<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateProductData extends BaseDto
{
    public int $tenantId;
    public string $name;
    public string $sku;
    public ?string $barcode = null;
    public string $type = 'physical';
    public string $status = 'active';
    public ?int $categoryId = null;
    public ?string $description = null;
    public ?string $shortDescription = null;
    public ?float $weight = null;
    public ?array $dimensions = null;
    public ?array $images = null;
    public ?array $tags = null;
    public bool $isTaxable = true;
    public ?string $taxClass = null;
    public bool $hasSerial = false;
    public bool $hasBatch = false;
    public bool $hasLot = false;
    public bool $isSerialized = false;
    public ?int $createdBy = null;
}
