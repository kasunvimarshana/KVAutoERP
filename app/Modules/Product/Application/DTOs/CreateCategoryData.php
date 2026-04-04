<?php

declare(strict_types=1);

namespace Modules\Product\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateCategoryData extends BaseDto
{
    public int $tenantId;
    public string $name;
    public string $slug;
    public ?string $description = null;
    public ?int $parentId = null;
    public ?string $image = null;
    public bool $isActive = true;
    public int $sortOrder = 0;
    public ?array $metadata = null;
    public ?int $createdBy = null;
}
