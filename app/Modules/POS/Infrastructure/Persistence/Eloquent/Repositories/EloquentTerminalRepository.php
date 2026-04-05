<?php declare(strict_types=1);
namespace Modules\POS\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\POS\Domain\Entities\Terminal;
use Modules\POS\Domain\RepositoryInterfaces\TerminalRepositoryInterface;
use Modules\POS\Infrastructure\Persistence\Eloquent\Models\TerminalModel;
class EloquentTerminalRepository implements TerminalRepositoryInterface {
    public function __construct(private readonly TerminalModel $model) {}
    public function findById(int $id): ?Terminal { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(Terminal $t): Terminal {
        $m=$t->getId()?$this->model->newQuery()->findOrFail($t->getId()):new TerminalModel();
        $m->tenant_id=$t->getTenantId();$m->name=$t->getName();$m->code=$t->getCode();$m->warehouse_id=$t->getWarehouseId();$m->is_active=$t->isActive();
        $m->save(); return $this->toEntity($m);
    }
    private function toEntity(TerminalModel $m): Terminal { return new Terminal($m->id,$m->tenant_id,$m->name,$m->code,$m->warehouse_id,(bool)$m->is_active); }
}
