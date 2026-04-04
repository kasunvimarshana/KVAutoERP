<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateOrgUnitData extends BaseDto
{
    public int $tenantId;
    public string $name;
    public string $code;
    public string $type;
    public ?int $parentId = null;
    public ?string $description = null;
    public bool $isActive = true;
    public ?array $metadata = null;
    public ?int $createdBy = null;
}
