<?php

declare(strict_types=1);

namespace Modules\Auth\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateRoleData extends BaseDto
{
    public int $tenantId;
    public string $name;
    public string $slug;
    public ?string $description = null;
    public bool $isSystem = false;
}
