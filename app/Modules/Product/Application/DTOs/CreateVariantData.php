<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateVariantData extends BaseDto
{
    public int $tenantId;
    public int $productId;
    public string $name;
    public string $sku;
    public ?string $barcode = null;
    public array $attributes = [];
    public ?float $price = null;
    public ?float $cost = null;
    public ?float $weight = null;
    public bool $isActive = true;
    public bool $stockManagement = true;
    public ?int $createdBy = null;
}
