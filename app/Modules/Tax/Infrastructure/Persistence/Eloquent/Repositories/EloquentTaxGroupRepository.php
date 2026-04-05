<?php declare(strict_types=1);
namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\Entities\TaxGroupRate;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupRateModel;
class EloquentTaxGroupRepository implements TaxGroupRepositoryInterface {
    public function __construct(private readonly TaxGroupModel $model, private readonly TaxGroupRateModel $rateModel) {}
    public function findById(int $id): ?TaxGroup {
        $m = $this->model->newQuery()->find($id);
        return $m ? $this->toEntity($m) : null;
    }
    public function findRatesByGroup(int $taxGroupId): array {
        return $this->rateModel->newQuery()->where('tax_group_id',$taxGroupId)->orderBy('sequence')->get()->map(fn($m)=>$this->toRateEntity($m))->all();
    }
    public function save(TaxGroup $g): TaxGroup {
        $m = $g->getId() ? $this->model->newQuery()->findOrFail($g->getId()) : new TaxGroupModel();
        $m->tenant_id=$g->getTenantId(); $m->name=$g->getName(); $m->code=$g->getCode(); $m->type=$g->getType(); $m->is_compound=$g->isCompound(); $m->is_active=$g->isActive();
        $m->save();
        return $this->toEntity($m);
    }
    public function saveRate(TaxGroupRate $r): TaxGroupRate {
        $m = $r->getId() ? $this->rateModel->newQuery()->findOrFail($r->getId()) : new TaxGroupRateModel();
        $m->tax_group_id=$r->getTaxGroupId(); $m->name=$r->getName(); $m->rate=$r->getRate(); $m->sequence=$r->getSequence();
        $m->save();
        return $this->toRateEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(TaxGroupModel $m): TaxGroup {
        return new TaxGroup($m->id,$m->tenant_id,$m->name,$m->code,$m->type,(bool)$m->is_compound,(bool)$m->is_active);
    }
    private function toRateEntity(TaxGroupRateModel $m): TaxGroupRate {
        return new TaxGroupRate($m->id,$m->tax_group_id,$m->name,(float)$m->rate,$m->sequence);
    }
}
