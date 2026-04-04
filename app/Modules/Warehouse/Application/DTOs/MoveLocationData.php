<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class MoveLocationData extends BaseDto
{
    public int $locationId;
    public ?int $newParentId = null;
    public ?int $updatedBy = null;
}
