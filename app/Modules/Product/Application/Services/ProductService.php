<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;

final class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $variantRepository,
        private readonly ProductComponentRepositoryInterface $componentRepository,
    ) {}

    public function getById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new NotFoundException('Product', $id);
        }

        return $product;
    }

    public function findBySku(int $tenantId, string $sku): ?Product
    {
        return $this->productRepository->findBySku($tenantId, $sku);
    }

    public function findByBarcode(int $tenantId, string $barcode): ?Product
    {
        return $this->productRepository->findByBarcode($tenantId, $barcode);
    }

    public function getByTenant(int $tenantId): Collection
    {
        return $this->productRepository->findByTenant($tenantId);
    }

    public function search(string $query, int $tenantId): Collection
    {
        return $this->productRepository->search($query, $tenantId);
    }

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->productRepository->update($id, $data);

        if ($product === null) {
            throw new NotFoundException('Product', $id);
        }

        return $product;
    }

    public function delete(int $id): bool
    {
        return $this->productRepository->delete($id);
    }

    public function addVariant(int $productId, array $data): ProductVariant
    {
        $this->getById($productId);

        $data['product_id'] = $productId;

        return $this->variantRepository->create($data);
    }

    public function removeVariant(int $variantId): bool
    {
        return $this->variantRepository->delete($variantId);
    }

    public function addComponent(int $productId, array $data): ProductComponent
    {
        $this->getById($productId);

        $data['product_id'] = $productId;

        return $this->componentRepository->addComponent($data);
    }

    public function removeComponent(int $componentId): bool
    {
        return $this->componentRepository->removeComponent($componentId);
    }
}
