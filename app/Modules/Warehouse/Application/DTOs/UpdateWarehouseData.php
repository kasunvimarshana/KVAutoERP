<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateWarehouseData extends BaseDto
{
    public ?string $name = null;
    public ?string $code = null;
    public ?string $type = null;
    public ?array $address = null;
    public ?bool $isActive = null;
    public ?int $managerUserId = null;
    public ?int $updatedBy = null;
}
