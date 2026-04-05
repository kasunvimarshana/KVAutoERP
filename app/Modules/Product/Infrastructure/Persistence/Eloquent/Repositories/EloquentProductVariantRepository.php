<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
class EloquentProductVariantRepository implements ProductVariantRepositoryInterface {
    public function __construct(private readonly ProductVariantModel $model) {}
    public function findById(int $id): ?ProductVariant { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByProduct(int $productId): array { return $this->model->newQuery()->where('product_id',$productId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(ProductVariant $v): ProductVariant {
        $m=$v->getId() ? $this->model->newQuery()->findOrFail($v->getId()) : new ProductVariantModel();
        $m->product_id=$v->getProductId(); $m->sku=$v->getSku(); $m->name=$v->getName();
        $m->attributes=$v->getAttributes(); $m->price_override=$v->getPriceOverride(); $m->cost_override=$v->getCostOverride(); $m->is_active=$v->isActive();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(ProductVariantModel $m): ProductVariant {
        return new ProductVariant($m->id,$m->product_id,$m->sku,$m->name,$m->attributes??[],$m->price_override??null,$m->cost_override??null,(bool)$m->is_active);
    }
}
