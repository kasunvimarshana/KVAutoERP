<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Application\DTOs;

use LaravelDDD\Examples\Product\Domain\Entities\Product;

/**
 * DTO representing a product in the Application layer.
 */
final class ProductDTO
{
    /**
     * @param  string  $id        Product UUID.
     * @param  string  $name      Product name.
     * @param  int     $price     Price in cents.
     * @param  string  $currency  Currency code.
     * @param  string  $status    Product status.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $price,
        public readonly string $currency,
        public readonly string $status,
    ) {}

    /**
     * Build a ProductDTO from a Product entity.
     *
     * @param  Product  $product
     * @return self
     */
    public static function fromProduct(Product $product): self
    {
        return new self(
            id: $product->getId()->value(),
            name: $product->getName()->value(),
            price: $product->getPrice()->amount(),
            currency: $product->getPrice()->currency(),
            status: $product->getStatus(),
        );
    }

    /**
     * Convert to associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'price'    => $this->price,
            'currency' => $this->currency,
            'status'   => $this->status,
        ];
    }
}
