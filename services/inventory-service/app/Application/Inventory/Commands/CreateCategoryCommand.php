<?php

declare(strict_types=1);

namespace App\Application\Inventory\Commands;

/**
 * Command to create a new product category.
 */
final readonly class CreateCategoryCommand
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public ?string $parentId,
        public string $description,
        public string $performedBy,
        public bool $isActive = true,
    ) {}
}
