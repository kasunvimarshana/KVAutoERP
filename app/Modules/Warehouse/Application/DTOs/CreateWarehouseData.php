<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateWarehouseData extends BaseDto
{
    public int $tenantId;
    public string $name;
    public string $code;
    public string $type = 'standard';
    public ?array $address = null;
    public bool $isActive = true;
    public ?int $managerUserId = null;
    public ?int $createdBy = null;
}
