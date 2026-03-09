<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Events;

use DateTimeImmutable;

/**
 * Fired when a new product is created.
 */
final class ProductCreated
{
    public readonly DateTimeImmutable $timestamp;

    public function __construct(
        public readonly string $productId,
        public readonly string $tenantId,
        public readonly string $sku,
        public readonly string $name,
        ?DateTimeImmutable $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'event'      => 'product.created',
            'product_id' => $this->productId,
            'tenant_id'  => $this->tenantId,
            'sku'        => $this->sku,
            'name'       => $this->name,
            'timestamp'  => $this->timestamp->format(DATE_ATOM),
        ];
    }
}
