<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Events;

use DateTimeImmutable;

/**
 * Fired when a product's stock falls to or below its minimum stock level.
 */
final class LowStockAlert
{
    public readonly DateTimeImmutable $timestamp;

    public function __construct(
        public readonly string $productId,
        public readonly string $tenantId,
        public readonly int $currentQty,
        public readonly int $minQty,
        ?DateTimeImmutable $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'event'       => 'stock.low_stock_alert',
            'product_id'  => $this->productId,
            'tenant_id'   => $this->tenantId,
            'current_qty' => $this->currentQty,
            'min_qty'     => $this->minQty,
            'timestamp'   => $this->timestamp->format(DATE_ATOM),
        ];
    }
}
