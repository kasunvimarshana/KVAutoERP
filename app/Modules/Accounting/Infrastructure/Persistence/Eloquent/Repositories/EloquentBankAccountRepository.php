<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\BankAccount;
use Modules\Accounting\Domain\RepositoryInterfaces\BankAccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankAccountModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function __construct(private readonly BankAccountModel $model) {}

    public function findById(string $id): ?BankAccount
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->get()
            ->map(fn (BankAccountModel $m) => $this->toEntity($m));
    }

    public function create(array $data): BankAccount
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(string $id, array $data): BankAccount
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('BankAccount', $id); }
        return (bool) $m->delete();
    }

    public function updateBalance(string $id, float $balance): BankAccount
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update(['balance' => $balance]);
        return $this->toEntity($m->fresh());
    }

    private function toEntity(BankAccountModel $m): BankAccount
    {
        return new BankAccount(
            id: $m->id, tenantId: $m->tenant_id, name: $m->name,
            accountNumber: $m->account_number, bankName: $m->bank_name,
            accountType: $m->account_type, balance: (float)$m->balance,
            currency: $m->currency ?? 'USD', isActive: (bool)$m->is_active,
            chartOfAccountId: $m->chart_of_account_id,
        );
    }
}
