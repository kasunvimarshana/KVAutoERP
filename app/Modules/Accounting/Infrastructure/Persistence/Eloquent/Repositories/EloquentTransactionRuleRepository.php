<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\TransactionRuleModel;
class EloquentTransactionRuleRepository implements TransactionRuleRepositoryInterface {
    public function __construct(private readonly TransactionRuleModel $model) {}
    public function findByTenant(int $tenantId): array {
        return $this->model->newQuery()->where('tenant_id',$tenantId)->orderBy('priority')->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(TransactionRule $r): TransactionRule {
        $m = $r->getId() ? $this->model->newQuery()->findOrFail($r->getId()) : new TransactionRuleModel();
        $m->tenant_id=$r->getTenantId(); $m->name=$r->getName(); $m->apply_to=$r->getApplyTo();
        $m->match_field=$r->getMatchField(); $m->match_value=$r->getMatchValue();
        $m->category_account_id=$r->getCategoryAccountId(); $m->priority=$r->getPriority();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(TransactionRuleModel $m): TransactionRule {
        return new TransactionRule($m->id,$m->tenant_id,$m->name,$m->apply_to,$m->match_field,$m->match_value,$m->category_account_id,$m->priority);
    }
}
