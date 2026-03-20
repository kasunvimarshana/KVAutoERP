<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Application\Commands;

/**
 * CQRS Command: create a new product.
 */
final readonly class CreateProductCommand
{
    /**
     * @param  string  $name          Product name.
     * @param  int     $priceInCents  Price in cents.
     * @param  string  $currency      ISO 4217 currency code.
     */
    public function __construct(
        public string $name,
        public int $priceInCents,
        public string $currency = 'USD',
    ) {}
}
