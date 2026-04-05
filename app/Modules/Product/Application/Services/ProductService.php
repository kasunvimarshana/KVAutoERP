<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;

class ProductService implements ProductServiceInterface
{
    private const VALID_TYPES = ['physical', 'service', 'digital', 'combo', 'variable'];

    public function __construct(
        private readonly ProductRepositoryInterface $repo,
    ) {}

    public function createProduct(array $data): Product
    {
        $tenantId = (int) $data['tenant_id'];
        $sku      = (string) $data['sku'];

        if ($this->repo->findBySku($sku, $tenantId) !== null) {
            throw new \InvalidArgumentException("SKU '{$sku}' already exists for this tenant.");
        }

        if (isset($data['type']) && !in_array($data['type'], self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(
                "Invalid product type '{$data['type']}'. Must be one of: " . implode(', ', self::VALID_TYPES)
            );
        }

        return $this->repo->create($data);
    }

    public function updateProduct(int $id, array $data): Product
    {
        $tenantId = (int) ($data['tenant_id'] ?? 0);
        $product  = $this->repo->findById($id, $tenantId);

        if ($product === null) {
            throw new \InvalidArgumentException("Product with id {$id} not found.");
        }

        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            $existing = $this->repo->findBySku((string) $data['sku'], $tenantId);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException("SKU '{$data['sku']}' already exists for this tenant.");
            }
        }

        if (isset($data['type']) && !in_array($data['type'], self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(
                "Invalid product type '{$data['type']}'. Must be one of: " . implode(', ', self::VALID_TYPES)
            );
        }

        return $this->repo->update($id, $data);
    }

    public function deleteProduct(int $id, int $tenantId): bool
    {
        $product = $this->repo->findById($id, $tenantId);

        if ($product === null) {
            throw new \InvalidArgumentException("Product with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function getProduct(int $id, int $tenantId): Product
    {
        $product = $this->repo->findById($id, $tenantId);

        if ($product === null) {
            throw new \InvalidArgumentException("Product with id {$id} not found.");
        }

        return $product;
    }

    public function getAll(int $tenantId): array
    {
        return $this->repo->allByTenant($tenantId);
    }

    public function getByCategory(int $categoryId, int $tenantId): array
    {
        return $this->repo->findByCategory($categoryId, $tenantId);
    }

    public function getByType(string $type, int $tenantId): array
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(
                "Invalid product type '{$type}'. Must be one of: " . implode(', ', self::VALID_TYPES)
            );
        }

        return $this->repo->findByType($type, $tenantId);
    }
}
