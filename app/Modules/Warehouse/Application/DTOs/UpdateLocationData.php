<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateLocationData extends BaseDto
{
    public ?string $name = null;
    public ?string $code = null;
    public ?string $type = null;
    public ?string $barcode = null;
    public ?float $capacity = null;
    public ?bool $isActive = null;
    public ?int $updatedBy = null;
}
