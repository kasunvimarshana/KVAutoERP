<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateOrgUnitData extends BaseDto
{
    public function __construct(
        public int $tenantId,
        public string $name,
        public ?int $parentId = null,
        public ?string $code = null,
        public string $type = 'department',
        public ?string $description = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {
        parent::__construct();
    }
}
