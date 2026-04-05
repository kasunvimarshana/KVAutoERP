<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Product\Application\Contracts\ProductComponentServiceInterface;
use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;

class ProductComponentService implements ProductComponentServiceInterface
{
    public function __construct(
        private readonly ProductComponentRepositoryInterface $repository,
    ) {}

    public function addComponent(
        int $productId,
        int $componentProductId,
        float $quantity,
        string $unit,
        ?string $notes,
    ): ProductComponent {
        return $this->repository->create([
            'product_id'           => $productId,
            'component_product_id' => $componentProductId,
            'quantity'             => $quantity,
            'unit'                 => $unit,
            'notes'                => $notes,
        ]);
    }

    public function removeComponent(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getComponents(int $productId): array
    {
        return $this->repository->findByProduct($productId);
    }
}
