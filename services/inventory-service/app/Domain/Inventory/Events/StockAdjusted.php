<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Events;

use DateTimeImmutable;

/**
 * Fired when a manual stock adjustment is performed.
 */
final class StockAdjusted
{
    public readonly DateTimeImmutable $timestamp;

    public function __construct(
        public readonly string $productId,
        public readonly int $previousQty,
        public readonly int $newQty,
        public readonly string $reason,
        public readonly string $tenantId,
        ?DateTimeImmutable $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'event'        => 'stock.adjusted',
            'product_id'   => $this->productId,
            'previous_qty' => $this->previousQty,
            'new_qty'      => $this->newQty,
            'reason'       => $this->reason,
            'tenant_id'    => $this->tenantId,
            'timestamp'    => $this->timestamp->format(DATE_ATOM),
        ];
    }
}
