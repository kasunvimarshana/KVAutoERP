<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\BankAccount;
use Modules\Accounting\Domain\RepositoryInterfaces\BankAccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankAccountModel;

class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function __construct(
        private readonly BankAccountModel $model,
    ) {}

    public function findById(int $id): ?BankAccount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenantId(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (BankAccountModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): BankAccount
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?BankAccount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function toEntity(BankAccountModel $model): BankAccount
    {
        return new BankAccount(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            accountNumber: $model->account_number,
            accountType: $model->account_type,
            currency: $model->currency,
            balance: (float) $model->balance,
            linkedAccountId: $model->linked_account_id,
            isActive: (bool) $model->is_active,
            lastSyncedAt: $model->last_synced_at,
            createdAt: $model->created_at,
        );
    }
}
