<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\ProductVariantServiceInterface;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;

class ProductVariantService implements ProductVariantServiceInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $repository,
    ) {}

    public function createVariant(int $productId, array $data): ProductVariant
    {
        return $this->repository->create(array_merge($data, ['product_id' => $productId]));
    }

    public function updateVariant(int $variantId, array $data): ProductVariant
    {
        $variant = $this->repository->update($variantId, $data);

        if ($variant === null) {
            throw new NotFoundException('ProductVariant', $variantId);
        }

        return $variant;
    }

    public function deleteVariant(int $variantId): bool
    {
        return $this->repository->delete($variantId);
    }

    public function getVariants(int $productId): array
    {
        return $this->repository->findByProduct($productId);
    }
}
