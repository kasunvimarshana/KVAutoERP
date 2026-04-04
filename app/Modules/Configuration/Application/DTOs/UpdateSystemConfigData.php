<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateSystemConfigData extends BaseDto
{
    public string $key;
    public ?string $value = null;
    public ?int $tenantId = null;
    public string $group = 'general';
    public ?string $description = null;
}
