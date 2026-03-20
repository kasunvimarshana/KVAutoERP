<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Application\Handlers;

use LaravelDDD\Examples\Product\Application\Commands\CreateProductCommand;
use LaravelDDD\Examples\Product\Domain\Entities\Product;
use LaravelDDD\Examples\Product\Domain\Repositories\ProductRepositoryInterface;
use LaravelDDD\Examples\Product\Domain\ValueObjects\ProductId;
use LaravelDDD\Examples\Product\Domain\ValueObjects\ProductName;
use LaravelDDD\SharedKernel\ValueObjects\Money;

/**
 * Handles the CreateProductCommand use case.
 */
class CreateProductHandler
{
    /**
     * @param  ProductRepositoryInterface  $repository
     */
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    /**
     * Execute the create-product use case.
     *
     * @param  CreateProductCommand  $command
     * @return Product  The newly created product.
     */
    public function handle(CreateProductCommand $command): Product
    {
        $product = Product::create(
            id: ProductId::generate(),
            name: new ProductName($command->name),
            price: Money::ofCents($command->priceInCents, $command->currency),
        );

        $this->repository->save($product);

        // Domain events can be dispatched here after persisting
        $product->releaseEvents();

        return $product;
    }
}
