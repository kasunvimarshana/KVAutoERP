<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComponentModel;
class EloquentProductComponentRepository implements ProductComponentRepositoryInterface {
    public function __construct(private readonly ProductComponentModel $model) {}
    public function findByParent(int $parentProductId): array { return $this->model->newQuery()->where('parent_product_id',$parentProductId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(ProductComponent $c): ProductComponent {
        $m=$c->getId() ? $this->model->newQuery()->findOrFail($c->getId()) : new ProductComponentModel();
        $m->parent_product_id=$c->getParentProductId(); $m->component_product_id=$c->getComponentProductId(); $m->quantity=$c->getQuantity(); $m->unit=$c->getUnit();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(ProductComponentModel $m): ProductComponent {
        return new ProductComponent($m->id,$m->parent_product_id,$m->component_product_id,(float)$m->quantity,$m->unit);
    }
}
