<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;
class EloquentCycleCountRepository implements CycleCountRepositoryInterface {
    public function __construct(private readonly CycleCountModel $model, private readonly CycleCountLineModel $lineModel) {}
    public function findById(int $id): ?CycleCount { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function save(CycleCount $c): CycleCount {
        $m=$c->getId()?$this->model->newQuery()->findOrFail($c->getId()):new CycleCountModel();
        $m->tenant_id=$c->getTenantId();$m->warehouse_id=$c->getWarehouseId();$m->status=$c->getStatus();$m->reference=$c->getReference();$m->scheduled_at=$c->getScheduledAt()?->format('Y-m-d H:i:s');$m->completed_at=$c->getCompletedAt()?->format('Y-m-d H:i:s');
        $m->save(); return $this->toEntity($m);
    }
    public function saveLine(CycleCountLine $l): CycleCountLine {
        $m=$l->getId()?$this->lineModel->newQuery()->findOrFail($l->getId()):new CycleCountLineModel();
        $m->cycle_count_id=$l->getCycleCountId();$m->product_id=$l->getProductId();$m->location_id=$l->getLocationId();$m->system_quantity=$l->getSystemQuantity();$m->counted_quantity=$l->getCountedQuantity();$m->variance=$l->getVariance();
        $m->save(); return new CycleCountLine($m->id,$m->cycle_count_id,$m->product_id,$m->location_id,(float)$m->system_quantity,$m->counted_quantity!==null?(float)$m->counted_quantity:null,$m->variance!==null?(float)$m->variance:null);
    }
    public function findLinesByCount(int $cycleCountId): array { return $this->lineModel->newQuery()->where('cycle_count_id',$cycleCountId)->get()->map(fn($m)=>new CycleCountLine($m->id,$m->cycle_count_id,$m->product_id,$m->location_id,(float)$m->system_quantity,$m->counted_quantity!==null?(float)$m->counted_quantity:null,$m->variance!==null?(float)$m->variance:null))->all(); }
    private function toEntity(CycleCountModel $m): CycleCount { return new CycleCount($m->id,$m->tenant_id,$m->warehouse_id,$m->status,$m->reference,$m->scheduled_at?new \DateTimeImmutable($m->scheduled_at->toDateTimeString()):null,$m->completed_at?new \DateTimeImmutable($m->completed_at->toDateTimeString()):null); }
}
