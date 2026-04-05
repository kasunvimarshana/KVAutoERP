<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Services;

use Modules\Pricing\Application\Contracts\PriceListItemServiceInterface;
use Modules\Pricing\Domain\Entities\PriceListItem;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListItemRepositoryInterface;

class PriceListItemService implements PriceListItemServiceInterface
{
    public function __construct(
        private readonly PriceListItemRepositoryInterface $repo,
    ) {}

    public function addItem(array $data): PriceListItem
    {
        if (!isset($data['price_type']) || !in_array($data['price_type'], ['fixed', 'percentage'], true)) {
            throw new \InvalidArgumentException("price_type must be 'fixed' or 'percentage'.");
        }

        if ((float) ($data['price'] ?? 0) < 0) {
            throw new \InvalidArgumentException('Price must be non-negative.');
        }

        if (isset($data['min_quantity']) && (float) $data['min_quantity'] < 0) {
            throw new \InvalidArgumentException('min_quantity must be non-negative.');
        }

        if (
            isset($data['max_quantity'], $data['min_quantity'])
            && (float) $data['max_quantity'] < (float) $data['min_quantity']
        ) {
            throw new \InvalidArgumentException('max_quantity must be greater than or equal to min_quantity.');
        }

        return $this->repo->create($data);
    }

    public function updateItem(int $id, array $data): PriceListItem
    {
        $tenantId = (int) ($data['tenant_id'] ?? 0);
        $item     = $this->repo->findById($id, $tenantId);

        if ($item === null) {
            throw new \InvalidArgumentException("Price list item with id {$id} not found.");
        }

        if (isset($data['price_type']) && !in_array($data['price_type'], ['fixed', 'percentage'], true)) {
            throw new \InvalidArgumentException("price_type must be 'fixed' or 'percentage'.");
        }

        if (isset($data['price']) && (float) $data['price'] < 0) {
            throw new \InvalidArgumentException('Price must be non-negative.');
        }

        return $this->repo->update($id, $data);
    }

    public function removeItem(int $id, int $tenantId): bool
    {
        $item = $this->repo->findById($id, $tenantId);

        if ($item === null) {
            throw new \InvalidArgumentException("Price list item with id {$id} not found.");
        }

        return $this->repo->delete($id, $tenantId);
    }

    public function getByPriceList(int $priceListId, int $tenantId): array
    {
        return $this->repo->findByPriceList($priceListId, $tenantId);
    }

    public function getByProduct(int $productId, int $tenantId): array
    {
        return $this->repo->findByProduct($productId, $tenantId);
    }
}
