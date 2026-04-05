<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Product\Application\Contracts\ProductComponentServiceInterface;
use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;

class ProductComponentService implements ProductComponentServiceInterface
{
    public function __construct(
        private readonly ProductComponentRepositoryInterface $repo,
    ) {}

    public function addComponent(array $data): ProductComponent
    {
        if ((int) $data['product_id'] === (int) $data['component_product_id']) {
            throw new \InvalidArgumentException('A product cannot be a component of itself.');
        }

        if ((float) ($data['quantity'] ?? 0) <= 0) {
            throw new \InvalidArgumentException('Component quantity must be greater than zero.');
        }

        return $this->repo->create($data);
    }

    public function updateComponent(int $id, array $data): ProductComponent
    {
        $tenantId  = (int) ($data['tenant_id'] ?? 0);
        $component = $this->repo->findById($id, $tenantId);

        if ($component === null) {
            throw new \InvalidArgumentException("Product component with id {$id} not found.");
        }

        if (isset($data['quantity']) && (float) $data['quantity'] <= 0) {
            throw new \InvalidArgumentException('Component quantity must be greater than zero.');
        }

        return $this->repo->update($id, $data);
    }

    public function removeComponent(int $id, int $tenantId): bool
    {
        $component = $this->repo->findById($id, $tenantId);

        if ($component === null) {
            throw new \InvalidArgumentException("Product component with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function getComponents(int $productId, int $tenantId): array
    {
        return $this->repo->findByProduct($productId, $tenantId);
    }

    public function clearComponents(int $productId, int $tenantId): void
    {
        $components = $this->repo->findByProduct($productId, $tenantId);

        foreach ($components as $component) {
            $this->repo->delete($component->id, $tenantId);
        }
    }
}
