<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankTransactionModel;

final class EloquentBankTransactionRepository implements BankTransactionRepositoryInterface
{
    public function __construct(
        private readonly BankTransactionModel $model,
    ) {}

    public function findById(int $id): ?BankTransaction
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByBankAccount(int $bankAccountId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('bank_account_id', $bankAccountId)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn (BankTransactionModel $m) => $this->toEntity($m));
    }

    public function findByStatus(int $tenantId, string $status): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn (BankTransactionModel $m) => $this->toEntity($m));
    }

    public function findByDateRange(int $tenantId, string $startDate, string $endDate): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn (BankTransactionModel $m) => $this->toEntity($m));
    }

    public function create(array $data): BankTransaction
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?BankTransaction
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function bulkUpdate(array $ids, array $data): int
    {
        return $this->model->newQueryWithoutScopes()
            ->whereIn('id', $ids)
            ->update($data);
    }

    private function toEntity(BankTransactionModel $model): BankTransaction
    {
        return new BankTransaction(
            id: $model->id,
            tenantId: $model->tenant_id,
            bankAccountId: $model->bank_account_id,
            date: \DateTimeImmutable::createFromMutable($model->date->toDateTime()),
            amount: (float) $model->amount,
            type: $model->type,
            description: $model->description,
            reference: $model->reference,
            source: $model->source,
            status: $model->status,
            accountId: $model->account_id,
            transactionRuleId: $model->transaction_rule_id,
            metadata: $model->metadata,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
