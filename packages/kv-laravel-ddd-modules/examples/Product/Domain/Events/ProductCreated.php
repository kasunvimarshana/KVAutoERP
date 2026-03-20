<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Domain\Events;

use DateTimeImmutable;
use LaravelDDD\Examples\Product\Domain\ValueObjects\ProductId;
use LaravelDDD\Examples\Product\Domain\ValueObjects\ProductName;
use LaravelDDD\SharedKernel\ValueObjects\Money;
use LaravelDDD\SharedKernel\ValueObjects\Uuid;

/**
 * Domain event raised when a new Product is created.
 */
final readonly class ProductCreated
{
    public DateTimeImmutable $occurredAt;

    /**
     * @param  string       $eventId    UUID of this event.
     * @param  ProductId    $productId  The product's identifier.
     * @param  ProductName  $name       The product's name.
     * @param  Money        $price      The product's price.
     */
    public function __construct(
        public string $eventId,
        public ProductId $productId,
        public ProductName $name,
        public Money $price,
    ) {
        $this->occurredAt = new DateTimeImmutable();
    }

    /**
     * Factory method.
     *
     * @param  ProductId    $productId
     * @param  ProductName  $name
     * @param  Money        $price
     * @return self
     */
    public static function create(ProductId $productId, ProductName $name, Money $price): self
    {
        return new self(
            eventId: (string) Uuid::generate(),
            productId: $productId,
            name: $name,
            price: $price,
        );
    }
}
