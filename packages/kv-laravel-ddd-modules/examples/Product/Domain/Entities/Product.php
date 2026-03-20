<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Domain\Entities;

use LaravelDDD\Examples\Product\Domain\Events\ProductCreated;
use LaravelDDD\Examples\Product\Domain\ValueObjects\ProductId;
use LaravelDDD\Examples\Product\Domain\ValueObjects\ProductName;
use LaravelDDD\SharedKernel\Contracts\EntityContract;
use LaravelDDD\SharedKernel\ValueObjects\Money;

/**
 * Product Domain Entity.
 *
 * The Product is the root entity in the Product bounded context.
 */
class Product implements EntityContract
{
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var list<object> */
    private array $domainEvents = [];

    /**
     * @param  ProductId    $id      Unique product identifier.
     * @param  ProductName  $name    Product name.
     * @param  Money        $price   Product price (in cents).
     * @param  string       $status  Product status.
     */
    public function __construct(
        private readonly ProductId $id,
        private ProductName $name,
        private Money $price,
        private string $status = self::STATUS_ACTIVE,
    ) {}

    /**
     * Create a new active product and record a ProductCreated event.
     *
     * @param  ProductId    $id
     * @param  ProductName  $name
     * @param  Money        $price
     * @return self
     */
    public static function create(ProductId $id, ProductName $name, Money $price): self
    {
        $product = new self($id, $name, $price);
        $product->recordEvent(ProductCreated::create($id, $name, $price));

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ProductId
    {
        return $this->id;
    }

    /**
     * Return the product name.
     *
     * @return ProductName
     */
    public function getName(): ProductName
    {
        return $this->name;
    }

    /**
     * Return the product price.
     *
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * Return the product status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Rename the product.
     *
     * @param  ProductName  $newName
     * @return void
     */
    public function rename(ProductName $newName): void
    {
        $this->name = $newName;
    }

    /**
     * Update the product price.
     *
     * @param  Money  $newPrice
     * @return void
     */
    public function reprice(Money $newPrice): void
    {
        $this->price = $newPrice;
    }

    /**
     * Activate the product.
     *
     * @return void
     */
    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * Deactivate the product.
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->status = self::STATUS_INACTIVE;
    }

    /**
     * Determine whether the product is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(EntityContract $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->id->equals($other->id);
    }

    /**
     * Record a domain event.
     *
     * @param  object  $event
     * @return void
     */
    protected function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Release and clear all recorded domain events.
     *
     * @return list<object>
     */
    public function releaseEvents(): array
    {
        $events            = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
