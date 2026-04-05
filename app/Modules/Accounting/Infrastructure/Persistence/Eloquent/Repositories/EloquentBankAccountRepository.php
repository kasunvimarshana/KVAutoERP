<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\BankAccount;
use Modules\Accounting\Domain\RepositoryInterfaces\BankAccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankAccountModel;

final class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function __construct(
        private readonly BankAccountModel $model,
    ) {}

    public function findById(int $id): ?BankAccount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (BankAccountModel $m) => $this->toEntity($m));
    }

    public function create(array $data): BankAccount
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?BankAccount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

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
            accountId: $model->account_id,
            name: $model->name,
            accountNumber: $model->account_number,
            accountType: $model->account_type,
            currencyCode: $model->currency_code,
            currentBalance: (float) $model->current_balance,
            lastSyncedAt: $model->last_synced_at
                ? \DateTimeImmutable::createFromMutable($model->last_synced_at->toDateTime())
                : null,
            isActive: (bool) $model->is_active,
            credentials: $model->credentials,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
