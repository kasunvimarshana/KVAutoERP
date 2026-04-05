<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\RepositoryInterfaces;

use Modules\Pricing\Domain\Entities\PriceListItem;

interface PriceListItemRepositoryInterface
{
    public function findById(int $id): ?PriceListItem;

    /** @return PriceListItem[] */
    public function findByPriceList(int $priceListId): array;

    /** @return PriceListItem[] */
    public function findByProduct(int $priceListId, int $productId, ?int $variantId): array;

    public function create(array $data): PriceListItem;

    public function update(int $id, array $data): ?PriceListItem;

    public function delete(int $id): bool;
}
