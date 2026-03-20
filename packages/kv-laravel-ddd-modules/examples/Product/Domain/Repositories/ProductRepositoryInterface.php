<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Domain\Repositories;

use LaravelDDD\Examples\Product\Domain\Entities\Product;
use LaravelDDD\SharedKernel\Contracts\RepositoryContract;

/**
 * Repository interface for the Product aggregate.
 */
interface ProductRepositoryInterface extends RepositoryContract
{
    /**
     * Find a product by its name.
     *
     * @param  string  $name
     * @return Product|null
     */
    public function findByName(string $name): ?Product;
}
