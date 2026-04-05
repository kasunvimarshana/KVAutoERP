<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankTransactionModel;

class EloquentBankTransactionRepository implements BankTransactionRepositoryInterface
{
    public function __construct(
        private readonly BankTransactionModel $model,
    ) {}

    public function findById(int $id): ?BankTransaction
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByBankAccount(int $bankAccountId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('bank_account_id', $bankAccountId)
            ->orderByDesc('date')
            ->get()
            ->map(fn (BankTransactionModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByStatus(int $tenantId, string $status): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->get()
            ->map(fn (BankTransactionModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): BankTransaction
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?BankTransaction
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

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model->newQueryWithoutScopes()
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    private function toEntity(BankTransactionModel $model): BankTransaction
    {
        return new BankTransaction(
            id: $model->id,
            tenantId: $model->tenant_id,
            bankAccountId: $model->bank_account_id,
            date: $model->date,
            description: $model->description,
            amount: (float) $model->amount,
            type: $model->type,
            status: $model->status,
            category: $model->category,
            accountId: $model->account_id,
            referenceNo: $model->reference_no,
            source: $model->source,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
