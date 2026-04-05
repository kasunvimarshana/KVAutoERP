<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Contracts;

use Modules\Pricing\Domain\Entities\PriceListItem;

interface PriceListItemServiceInterface
{
    public function addItem(array $data): PriceListItem;

    public function updateItem(int $id, array $data): PriceListItem;

    public function removeItem(int $id, int $tenantId): bool;

    public function getByPriceList(int $priceListId, int $tenantId): array;

    public function getByProduct(int $productId, int $tenantId): array;
}
