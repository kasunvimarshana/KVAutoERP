<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Events;

use DateTimeImmutable;

/**
 * Fired when previously-reserved stock is released.
 */
final class StockReleased
{
    public readonly DateTimeImmutable $timestamp;

    public function __construct(
        public readonly string $productId,
        public readonly string $orderId,
        public readonly int $quantity,
        public readonly string $tenantId,
        ?DateTimeImmutable $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'event'      => 'stock.released',
            'product_id' => $this->productId,
            'order_id'   => $this->orderId,
            'quantity'   => $this->quantity,
            'tenant_id'  => $this->tenantId,
            'timestamp'  => $this->timestamp->format(DATE_ATOM),
        ];
    }
}
