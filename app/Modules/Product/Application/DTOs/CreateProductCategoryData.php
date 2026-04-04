<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateProductCategoryData extends BaseDto
{
    public function __construct(
        public int $tenantId,
        public string $name,
        public string $slug,
        public ?int $parentId = null,
        public ?string $description = null,
        public ?string $image = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {}
}
