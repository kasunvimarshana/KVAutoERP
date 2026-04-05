<?php declare(strict_types=1);
namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\WarehouseModel;
class EloquentWarehouseRepository implements WarehouseRepositoryInterface {
    public function __construct(private readonly WarehouseModel $model) {}
    public function findById(int $id): ?Warehouse { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findDefault(int $tenantId): ?Warehouse { $m=$this->model->newQuery()->where('tenant_id',$tenantId)->where('is_default',true)->first(); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(Warehouse $w): Warehouse {
        $m=$w->getId() ? $this->model->newQuery()->findOrFail($w->getId()) : new WarehouseModel();
        $m->tenant_id=$w->getTenantId(); $m->name=$w->getName(); $m->code=$w->getCode();
        $m->type=$w->getType(); $m->address=$w->getAddress(); $m->is_active=$w->isActive(); $m->is_default=$w->isDefault();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(WarehouseModel $m): Warehouse {
        return new Warehouse($m->id,$m->tenant_id,$m->name,$m->code,$m->type,$m->address,(bool)$m->is_active,(bool)$m->is_default);
    }
}
