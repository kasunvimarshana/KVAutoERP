<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankTransactionModel;
class EloquentBankTransactionRepository implements BankTransactionRepositoryInterface {
    public function __construct(private readonly BankTransactionModel $model) {}
    public function findById(int $id): ?BankTransaction {
        $m = $this->model->newQuery()->find($id);
        return $m ? $this->toEntity($m) : null;
    }
    public function findByBankAccount(int $bankAccountId): array {
        return $this->model->newQuery()->where('bank_account_id',$bankAccountId)->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function findUncategorized(int $tenantId): array {
        return $this->model->newQuery()->where('tenant_id',$tenantId)->where('status','pending')->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(BankTransaction $t): BankTransaction {
        $m = $t->getId() ? $this->model->newQuery()->findOrFail($t->getId()) : new BankTransactionModel();
        $m->bank_account_id=$t->getBankAccountId(); $m->tenant_id=$t->getTenantId(); $m->type=$t->getType();
        $m->amount=$t->getAmount(); $m->transaction_date=$t->getTransactionDate()->format('Y-m-d');
        $m->description=$t->getDescription(); $m->status=$t->getStatus(); $m->source=$t->getSource();
        $m->account_id=$t->getAccountId(); $m->reference=$t->getReference();
        $m->save();
        return $this->toEntity($m);
    }
    public function saveMany(array $transactions): array {
        return array_map(fn($t) => $this->save($t), $transactions);
    }
    private function toEntity(BankTransactionModel $m): BankTransaction {
        return new BankTransaction($m->id,$m->bank_account_id,$m->tenant_id,$m->type,(float)$m->amount,new \DateTimeImmutable($m->transaction_date->toDateString()),$m->description,$m->status,$m->source,$m->account_id,$m->reference);
    }
}
