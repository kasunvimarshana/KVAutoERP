<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Product\Application\Contracts\ProductVariantServiceInterface;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;

class ProductVariantService implements ProductVariantServiceInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $repo,
    ) {}

    public function createVariant(array $data): ProductVariant
    {
        $tenantId = (int) $data['tenant_id'];
        $sku      = (string) $data['sku'];

        if ($this->repo->findBySku($sku, $tenantId) !== null) {
            throw new \InvalidArgumentException("Variant SKU '{$sku}' already exists for this tenant.");
        }

        return $this->repo->create($data);
    }

    public function updateVariant(int $id, array $data): ProductVariant
    {
        $tenantId = (int) ($data['tenant_id'] ?? 0);
        $variant  = $this->repo->findById($id, $tenantId);

        if ($variant === null) {
            throw new \InvalidArgumentException("Product variant with id {$id} not found.");
        }

        if (isset($data['sku']) && $data['sku'] !== $variant->sku) {
            $existing = $this->repo->findBySku((string) $data['sku'], $tenantId);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException("Variant SKU '{$data['sku']}' already exists for this tenant.");
            }
        }

        return $this->repo->update($id, $data);
    }

    public function deleteVariant(int $id, int $tenantId): bool
    {
        $variant = $this->repo->findById($id, $tenantId);

        if ($variant === null) {
            throw new \InvalidArgumentException("Product variant with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function getVariant(int $id, int $tenantId): ProductVariant
    {
        $variant = $this->repo->findById($id, $tenantId);

        if ($variant === null) {
            throw new \InvalidArgumentException("Product variant with id {$id} not found.");
        }

        return $variant;
    }

    public function getByProduct(int $productId, int $tenantId): array
    {
        return $this->repo->findByProduct($productId, $tenantId);
    }

    public function getAllByTenant(int $tenantId): array
    {
        return $this->repo->allByTenant($tenantId);
    }
}
