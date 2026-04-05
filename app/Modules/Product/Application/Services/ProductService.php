<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function create(array $data): Product
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->repository->update($id, $data);

        if ($product === null) {
            throw new NotFoundException('Product', $id);
        }

        return $product;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Product
    {
        $product = $this->repository->findById($id);

        if ($product === null) {
            throw new NotFoundException('Product', $id);
        }

        return $product;
    }

    public function findBySku(int $tenantId, string $sku): Product
    {
        $product = $this->repository->findBySku($tenantId, $sku);

        if ($product === null) {
            throw new NotFoundException("Product with SKU '{$sku}'");
        }

        return $product;
    }

    public function findByBarcode(int $tenantId, string $barcode): Product
    {
        $product = $this->repository->findByBarcode($tenantId, $barcode);

        if ($product === null) {
            throw new NotFoundException("Product with barcode '{$barcode}'");
        }

        return $product;
    }

    public function search(int $tenantId, string $query): array
    {
        return $this->repository->search($tenantId, $query);
    }

    public function findByCategory(int $tenantId, int $categoryId): array
    {
        return $this->repository->findByCategory($tenantId, $categoryId);
    }

    public function findByType(int $tenantId, string $type): array
    {
        return $this->repository->findByType($tenantId, $type);
    }

    public function all(int $tenantId): array
    {
        return $this->repository->all($tenantId);
    }
}
