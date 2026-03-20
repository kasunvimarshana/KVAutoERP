<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Application\Queries;

/**
 * CQRS Query: fetch a single product by its ID.
 */
final readonly class GetProductQuery
{
    /**
     * @param  string  $productId  UUID string of the product.
     */
    public function __construct(
        public string $productId,
    ) {}
}
