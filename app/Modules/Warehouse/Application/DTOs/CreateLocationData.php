<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateLocationData extends BaseDto
{
    public int $tenantId;
    public int $warehouseId;
    public ?int $parentId = null;
    public string $name;
    public string $code;
    public string $type = 'bin';
    public ?string $barcode = null;
    public ?float $capacity = null;
    public bool $isActive = true;
    public ?int $createdBy = null;
}
