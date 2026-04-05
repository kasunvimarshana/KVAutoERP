<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\BankTransaction;

interface BankTransactionRepositoryInterface
{
    public function findById(string $id): ?BankTransaction;
    public function allByBankAccount(string $bankAccountId): Collection;
    public function allByTenant(string $tenantId): Collection;
    public function bulkInsert(array $transactions): int;
    public function updateCategory(string $id, string $categoryId, ?string $accountId): BankTransaction;
    public function bulkUpdateCategory(array $ids, string $categoryId): int;
    public function create(array $data): BankTransaction;
    public function update(string $id, array $data): BankTransaction;
    public function delete(string $id): bool;
}
