<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateOrgUnitData extends BaseDto
{
    public function __construct(
        public ?string $name = null,
        public ?int $parentId = null,
        public bool $clearParentId = false,
        public ?string $code = null,
        public ?string $type = null,
        public ?string $description = null,
        public ?bool $isActive = null,
        public ?int $sortOrder = null,
    ) {
        parent::__construct();
    }
}
