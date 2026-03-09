<?php

declare(strict_types=1);

namespace App\Application\Inventory\Queries;

/**
 * Query to generate an inventory report.
 */
final readonly class GetInventoryReportQuery
{
    public function __construct(
        public string $tenantId,
        public ?string $warehouseId = null,
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public string $groupBy = 'category',
    ) {}
}
