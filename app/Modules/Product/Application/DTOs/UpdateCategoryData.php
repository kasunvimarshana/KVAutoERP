<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateCategoryData extends BaseDto
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $description = null;
    public ?string $image = null;
    public ?bool $isActive = null;
    public ?int $sortOrder = null;
    public ?array $metadata = null;
    public ?int $updatedBy = null;
}
