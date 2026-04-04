<?php

declare(strict_types=1);

namespace Modules\Auth\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class AssignPermissionsData extends BaseDto
{
    public array $permissionIds = [];
}
