<?php declare(strict_types=1);
namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\CRM\Domain\Entities\Contact;
use Modules\CRM\Domain\RepositoryInterfaces\ContactRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ContactModel;
class EloquentContactRepository implements ContactRepositoryInterface {
    public function __construct(private readonly ContactModel $model) {}
    public function findById(int $id): ?Contact { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId, ?string $type=null): array {
        $q=$this->model->newQuery()->where('tenant_id',$tenantId);
        if($type) $q->where('type',$type);
        return $q->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(Contact $c): Contact {
        $m=$c->getId()?$this->model->newQuery()->findOrFail($c->getId()):new ContactModel();
        $m->tenant_id=$c->getTenantId();$m->type=$c->getType();$m->name=$c->getName();$m->email=$c->getEmail();$m->phone=$c->getPhone();$m->company=$c->getCompany();$m->address=$c->getAddress();$m->is_active=$c->isActive();$m->metadata=$c->getMetadata();
        $m->save(); return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(ContactModel $m): Contact { return new Contact($m->id,$m->tenant_id,$m->type,$m->name,$m->email,$m->phone,$m->company,$m->address,(bool)$m->is_active,$m->metadata); }
}
