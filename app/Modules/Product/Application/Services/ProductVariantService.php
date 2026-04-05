<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\ProductVariantServiceInterface;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;

final class ProductVariantService implements ProductVariantServiceInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $variantRepository,
    ) {}

    public function getById(int $id): ProductVariant
    {
        $variant = $this->variantRepository->findById($id);

        if ($variant === null) {
            throw new NotFoundException('ProductVariant', $id);
        }

        return $variant;
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->variantRepository->findByProduct($productId);
    }

    public function create(array $data): ProductVariant
    {
        return $this->variantRepository->create($data);
    }

    public function update(int $id, array $data): ProductVariant
    {
        $variant = $this->variantRepository->update($id, $data);

        if ($variant === null) {
            throw new NotFoundException('ProductVariant', $id);
        }

        return $variant;
    }

    public function delete(int $id): bool
    {
        return $this->variantRepository->delete($id);
    }
}
