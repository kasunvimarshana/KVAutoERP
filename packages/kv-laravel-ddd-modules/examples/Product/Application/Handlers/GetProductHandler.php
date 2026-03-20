<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Application\Handlers;

use LaravelDDD\Examples\Product\Application\DTOs\ProductDTO;
use LaravelDDD\Examples\Product\Application\Queries\GetProductQuery;
use LaravelDDD\Examples\Product\Domain\Repositories\ProductRepositoryInterface;
use LaravelDDD\Examples\Product\Domain\ValueObjects\ProductId;

/**
 * Handles the GetProductQuery use case.
 */
class GetProductHandler
{
    /**
     * @param  ProductRepositoryInterface  $repository
     */
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    /**
     * Execute the get-product query.
     *
     * @param  GetProductQuery  $query
     * @return ProductDTO|null  The product DTO, or null if not found.
     */
    public function handle(GetProductQuery $query): ?ProductDTO
    {
        $productId = ProductId::fromString($query->productId);
        $product   = $this->repository->findById($productId->value());

        if ($product === null) {
            return null;
        }

        // @phpstan-ignore-next-line
        return ProductDTO::fromProduct($product);
    }
}
