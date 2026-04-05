<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\AccountModel;
class EloquentAccountRepository implements AccountRepositoryInterface {
    public function __construct(private readonly AccountModel $model) {}
    public function findById(int $id): ?Account {
        $m = $this->model->newQuery()->find($id);
        return $m ? $this->toEntity($m) : null;
    }
    public function findByCode(int $tenantId, string $code): ?Account {
        $m = $this->model->newQuery()->where('tenant_id',$tenantId)->where('code',$code)->first();
        return $m ? $this->toEntity($m) : null;
    }
    public function findByTenant(int $tenantId): array {
        return $this->model->newQuery()->where('tenant_id',$tenantId)->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function findByType(int $tenantId, string $type): array {
        return $this->model->newQuery()->where('tenant_id',$tenantId)->where('type',$type)->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(Account $a): Account {
        $m = $a->getId() ? $this->model->newQuery()->findOrFail($a->getId()) : new AccountModel();
        $m->tenant_id=$a->getTenantId(); $m->code=$a->getCode(); $m->name=$a->getName(); $m->type=$a->getType();
        $m->sub_type=$a->getSubType(); $m->parent_id=$a->getParentId(); $m->is_active=$a->isActive();
        $m->normal_balance=$a->getNormalBalance(); $m->description=$a->getDescription();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(AccountModel $m): Account {
        return new Account($m->id,$m->tenant_id,$m->code,$m->name,$m->type,$m->sub_type,$m->parent_id,(bool)$m->is_active,$m->normal_balance,$m->description);
    }
}
