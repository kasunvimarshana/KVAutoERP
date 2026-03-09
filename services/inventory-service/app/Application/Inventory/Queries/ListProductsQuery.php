<?php

declare(strict_types=1);

namespace App\Application\Inventory\Queries;

/**
 * Query to list/search products with filtering, sorting, and pagination.
 */
final readonly class ListProductsQuery
{
    public function __construct(
        public string $tenantId,
        public array $filters = [],
        public array $sorts = ['created_at' => 'desc'],
        public int $perPage = 20,
        public int $page = 1,
        public ?string $search = null,
        // Convenience filter flags:
        public ?string $sku = null,
        public ?string $name = null,
        public ?string $categoryId = null,
        public ?string $status = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public bool $lowStockOnly = false,
        public bool $outOfStockOnly = false,
    ) {}
}
