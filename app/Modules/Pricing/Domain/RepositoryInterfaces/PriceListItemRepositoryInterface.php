<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\RepositoryInterfaces;

use Modules\Pricing\Domain\Entities\PriceListItem;

interface PriceListItemRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?PriceListItem;

    public function findByPriceList(int $priceListId, int $tenantId): array;

    public function findByProduct(int $productId, int $tenantId): array;

    public function create(array $data): PriceListItem;

    public function update(int $id, array $data): PriceListItem;

    public function delete(int $id, int $tenantId): bool;
}
