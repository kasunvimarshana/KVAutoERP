<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankTransactionModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentBankTransactionRepository implements BankTransactionRepositoryInterface
{
    public function __construct(private readonly BankTransactionModel $model) {}

    public function findById(string $id): ?BankTransaction
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function allByBankAccount(string $bankAccountId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('bank_account_id', $bankAccountId)->orderByDesc('date')->get()
            ->map(fn (BankTransactionModel $m) => $this->toEntity($m));
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->orderByDesc('date')->get()
            ->map(fn (BankTransactionModel $m) => $this->toEntity($m));
    }

    public function bulkInsert(array $transactions): int
    {
        DB::table($this->model->getTable())->insert($transactions);
        return count($transactions);
    }

    public function updateCategory(string $id, string $categoryId, ?string $accountId): BankTransaction
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update(['category_id' => $categoryId, 'account_id' => $accountId, 'status' => 'categorized']);
        return $this->toEntity($m->fresh());
    }

    public function bulkUpdateCategory(array $ids, string $categoryId): int
    {
        return $this->model->withoutGlobalScopes()
            ->whereIn('id', $ids)
            ->update(['category_id' => $categoryId, 'status' => 'categorized']);
    }

    public function create(array $data): BankTransaction
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(string $id, array $data): BankTransaction
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('BankTransaction', $id); }
        return (bool) $m->delete();
    }

    private function toEntity(BankTransactionModel $m): BankTransaction
    {
        return new BankTransaction(
            id: $m->id, tenantId: $m->tenant_id, bankAccountId: $m->bank_account_id,
            date: new \DateTimeImmutable($m->date->toDateString()),
            description: $m->description, amount: (float)$m->amount,
            type: $m->type, status: $m->status, source: $m->source,
            categoryId: $m->category_id, journalEntryId: $m->journal_entry_id,
            reference: $m->reference, metadata: $m->metadata,
        );
    }
}
