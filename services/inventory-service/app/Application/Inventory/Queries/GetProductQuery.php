<?php

declare(strict_types=1);

namespace App\Application\Inventory\Queries;

/**
 * Query to fetch a single product by ID.
 */
final readonly class GetProductQuery
{
    public function __construct(
        public string $productId,
        public string $tenantId,
        public bool $withCategory = true,
        public bool $withMovements = false,
    ) {}
}
