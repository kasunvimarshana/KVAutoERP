<?php

declare(strict_types=1);

namespace Modules\Auth\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateRoleData extends BaseDto
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $description = null;
}
