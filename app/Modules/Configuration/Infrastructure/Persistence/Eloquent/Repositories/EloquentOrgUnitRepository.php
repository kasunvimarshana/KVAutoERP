<?php declare(strict_types=1);
namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\OrgUnitModel;
class EloquentOrgUnitRepository implements OrgUnitRepositoryInterface {
    public function __construct(private readonly OrgUnitModel $model) {}
    public function findById(int $id): ?OrgUnit {
        $m = $this->model->newQuery()->find($id);
        return $m ? $this->toEntity($m) : null;
    }
    public function findByTenant(int $tenantId): array {
        return $this->model->newQuery()->where('tenant_id',$tenantId)->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function findDescendants(int $id): array {
        $unit = $this->model->newQuery()->find($id);
        if (!$unit) return [];
        return $this->model->newQuery()->where('path','like',$unit->path.'%')->where('id','!=',$id)->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function findAncestors(int $id): array {
        $unit = $this->model->newQuery()->find($id);
        if (!$unit) return [];
        $parts = array_filter(explode('/',$unit->path));
        array_pop($parts);
        if (empty($parts)) return [];
        return $this->model->newQuery()->whereIn('id',$parts)->orderBy('level')->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(OrgUnit $unit): OrgUnit {
        if ($unit->getId()) { $m = $this->model->newQuery()->findOrFail($unit->getId()); } else { $m = new OrgUnitModel(); }
        $m->tenant_id = $unit->getTenantId(); $m->name = $unit->getName(); $m->code = $unit->getCode(); $m->type = $unit->getType(); $m->parent_id = $unit->getParentId(); $m->path = $unit->getPath(); $m->level = $unit->getLevel(); $m->is_active = $unit->isActive();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(OrgUnitModel $m): OrgUnit {
        return new OrgUnit($m->id,$m->tenant_id,$m->name,$m->code,$m->type,$m->parent_id,$m->path,$m->level,(bool)$m->is_active);
    }
}
