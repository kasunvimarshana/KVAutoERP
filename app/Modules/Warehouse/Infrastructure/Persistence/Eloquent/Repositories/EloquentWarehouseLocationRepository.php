<?php declare(strict_types=1);
namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseLocationRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\WarehouseLocationModel;
class EloquentWarehouseLocationRepository implements WarehouseLocationRepositoryInterface {
    public function __construct(private readonly WarehouseLocationModel $model) {}
    public function findById(int $id): ?WarehouseLocation { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByWarehouse(int $warehouseId): array { return $this->model->newQuery()->where('warehouse_id',$warehouseId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function findDescendants(int $id): array {
        $loc=$this->model->newQuery()->find($id);
        if (!$loc) return [];
        return $this->model->newQuery()->where('path','like',$loc->path.'%')->where('id','!=',$id)->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(WarehouseLocation $l): WarehouseLocation {
        $m=$l->getId() ? $this->model->newQuery()->findOrFail($l->getId()) : new WarehouseLocationModel();
        $m->warehouse_id=$l->getWarehouseId(); $m->name=$l->getName(); $m->code=$l->getCode();
        $m->type=$l->getType(); $m->parent_id=$l->getParentId(); $m->path=$l->getPath();
        $m->level=$l->getLevel(); $m->is_active=$l->isActive(); $m->is_pickable=$l->isPickable(); $m->is_receivable=$l->isReceivable();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(WarehouseLocationModel $m): WarehouseLocation {
        return new WarehouseLocation($m->id,$m->warehouse_id,$m->name,$m->code,$m->type,$m->parent_id,$m->path,$m->level,(bool)$m->is_active,(bool)$m->is_pickable,(bool)$m->is_receivable);
    }
}
