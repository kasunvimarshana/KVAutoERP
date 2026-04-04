<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateOrgUnitData extends BaseDto
{
    public ?string $name = null;
    public ?string $code = null;
    public ?string $type = null;
    public ?string $description = null;
    public ?bool $isActive = null;
    public ?array $metadata = null;
    public ?int $updatedBy = null;
}
