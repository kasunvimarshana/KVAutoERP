<?php

declare(strict_types=1);

namespace App\Application\Inventory\Queries;

/**
 * Query to list stock movements for a product with optional filters.
 */
final readonly class GetStockMovementsQuery
{
    public function __construct(
        public string $productId,
        public string $tenantId,
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?string $type = null,
        public int $perPage = 30,
        public int $page = 1,
    ) {}
}
