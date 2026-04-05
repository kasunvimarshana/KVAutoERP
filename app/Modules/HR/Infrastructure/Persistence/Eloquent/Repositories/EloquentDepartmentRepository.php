<?php declare(strict_types=1);
namespace Modules\HR\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\HR\Domain\Entities\Department;
use Modules\HR\Domain\RepositoryInterfaces\DepartmentRepositoryInterface;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\DepartmentModel;
class EloquentDepartmentRepository implements DepartmentRepositoryInterface {
    public function __construct(private readonly DepartmentModel $model) {}
    public function findById(int $id): ?Department { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(Department $d): Department {
        $m=$d->getId()?$this->model->newQuery()->findOrFail($d->getId()):new DepartmentModel();
        $m->tenant_id=$d->getTenantId();$m->name=$d->getName();$m->code=$d->getCode();$m->parent_id=$d->getParentId();$m->manager_id=$d->getManagerId();$m->is_active=$d->isActive();
        $m->save(); return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(DepartmentModel $m): Department { return new Department($m->id,$m->tenant_id,$m->name,$m->code,$m->parent_id,$m->manager_id,(bool)$m->is_active); }
}
