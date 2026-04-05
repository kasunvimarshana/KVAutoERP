<?php declare(strict_types=1);
namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\CRM\Domain\Entities\Lead;
use Modules\CRM\Domain\RepositoryInterfaces\LeadRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\LeadModel;
class EloquentLeadRepository implements LeadRepositoryInterface {
    public function __construct(private readonly LeadModel $model) {}
    public function findById(int $id): ?Lead { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId, ?string $status=null): array {
        $q=$this->model->newQuery()->where('tenant_id',$tenantId);
        if($status) $q->where('status',$status);
        return $q->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(Lead $l): Lead {
        $m=$l->getId()?$this->model->newQuery()->findOrFail($l->getId()):new LeadModel();
        $m->tenant_id=$l->getTenantId();$m->title=$l->getTitle();$m->contact_id=$l->getContactId();$m->status=$l->getStatus();$m->value=$l->getValue();$m->currency=$l->getCurrency();$m->assigned_to=$l->getAssignedTo();$m->expected_close_date=$l->getExpectedCloseDate()?->format('Y-m-d');
        $m->save(); return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(LeadModel $m): Lead { return new Lead($m->id,$m->tenant_id,$m->title,$m->contact_id,$m->status,(float)$m->value,$m->currency,$m->assigned_to,$m->expected_close_date?new \DateTimeImmutable($m->expected_close_date->toDateString()):null); }
}
