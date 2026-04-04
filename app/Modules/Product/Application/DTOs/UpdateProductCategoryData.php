<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateProductCategoryData extends BaseDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?int $parentId = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?bool $isActive = null,
        public ?int $sortOrder = null,
    ) {}
}
