<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Product\Application\Contracts\GetProductServiceInterface;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Exceptions\ProductNotFoundException;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class GetProductService implements GetProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(int $id): Product
    {
        $product = $this->repository->findById($id);
        if ($product === null) {
            throw new ProductNotFoundException($id);
        }

        return $product;
    }
}
