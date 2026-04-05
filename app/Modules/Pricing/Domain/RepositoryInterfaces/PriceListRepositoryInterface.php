<?php declare(strict_types=1);
namespace Modules\Pricing\Domain\RepositoryInterfaces;
use Modules\Pricing\Domain\Entities\PriceList;
use Modules\Pricing\Domain\Entities\PriceListItem;
interface PriceListRepositoryInterface {
    public function findById(int $id): ?PriceList;
    public function findDefault(int $tenantId): ?PriceList;
    public function findItemsByProduct(int $priceListId, int $productId): array;
    public function save(PriceList $list): PriceList;
    public function saveItem(PriceListItem $item): PriceListItem;
    public function deleteItem(int $id): void;
}
