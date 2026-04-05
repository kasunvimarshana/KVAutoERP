<?php declare(strict_types=1);
namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Pricing\Domain\Entities\PriceList;
use Modules\Pricing\Domain\Entities\PriceListItem;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListItemModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListModel;
class EloquentPriceListRepository implements PriceListRepositoryInterface {
    public function __construct(
        private readonly PriceListModel $model,
        private readonly PriceListItemModel $itemModel,
    ) {}
    public function findById(int $id): ?PriceList { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findDefault(int $tenantId): ?PriceList { $m=$this->model->newQuery()->where('tenant_id',$tenantId)->where('is_default',true)->first(); return $m?$this->toEntity($m):null; }
    public function findItemsByProduct(int $priceListId, int $productId): array { return $this->itemModel->newQuery()->where('price_list_id',$priceListId)->where('product_id',$productId)->get()->map(fn($m)=>$this->toItemEntity($m))->all(); }
    public function save(PriceList $pl): PriceList {
        $m=$pl->getId() ? $this->model->newQuery()->findOrFail($pl->getId()) : new PriceListModel();
        $m->tenant_id=$pl->getTenantId(); $m->name=$pl->getName(); $m->code=$pl->getCode();
        $m->currency=$pl->getCurrency(); $m->is_default=$pl->isDefault(); $m->is_active=$pl->isActive();
        $m->save();
        return $this->toEntity($m);
    }
    public function saveItem(PriceListItem $i): PriceListItem {
        $m=$i->getId() ? $this->itemModel->newQuery()->findOrFail($i->getId()) : new PriceListItemModel();
        $m->price_list_id=$i->getPriceListId(); $m->product_id=$i->getProductId(); $m->price_type=$i->getPriceType();
        $m->price=$i->getPrice(); $m->min_quantity=$i->getMinQuantity();
        $m->valid_from=$i->getValidFrom()?->format('Y-m-d');
        $m->valid_to=$i->getValidTo()?->format('Y-m-d');
        $m->save();
        return $this->toItemEntity($m);
    }
    public function deleteItem(int $id): void { $this->itemModel->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(PriceListModel $m): PriceList {
        return new PriceList($m->id,$m->tenant_id,$m->name,$m->code,$m->currency,(bool)$m->is_default,(bool)$m->is_active);
    }
    private function toItemEntity(PriceListItemModel $m): PriceListItem {
        return new PriceListItem(
            $m->id,$m->price_list_id,$m->product_id,$m->price_type,(float)$m->price,(float)$m->min_quantity,
            $m->valid_from ? new \DateTimeImmutable($m->valid_from->toDateString()) : null,
            $m->valid_to ? new \DateTimeImmutable($m->valid_to->toDateString()) : null,
        );
    }
}
