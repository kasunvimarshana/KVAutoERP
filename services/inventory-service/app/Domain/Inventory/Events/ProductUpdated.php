<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Events;

use DateTimeImmutable;

/**
 * Fired when a product's data is updated.
 */
final class ProductUpdated
{
    public readonly DateTimeImmutable $timestamp;

    public function __construct(
        public readonly string $productId,
        public readonly string $tenantId,
        public readonly array $changedFields,
        ?DateTimeImmutable $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'event'          => 'product.updated',
            'product_id'     => $this->productId,
            'tenant_id'      => $this->tenantId,
            'changed_fields' => $this->changedFields,
            'timestamp'      => $this->timestamp->format(DATE_ATOM),
        ];
    }
}
