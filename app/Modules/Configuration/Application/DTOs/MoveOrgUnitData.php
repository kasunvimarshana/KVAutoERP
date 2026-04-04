<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class MoveOrgUnitData extends BaseDto
{
    public ?int $parentId = null;
}
