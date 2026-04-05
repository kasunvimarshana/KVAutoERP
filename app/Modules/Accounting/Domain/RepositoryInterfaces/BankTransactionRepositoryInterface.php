<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\BankTransaction;

interface BankTransactionRepositoryInterface
{
    public function findById(int $id): ?BankTransaction;

    /** @return Collection<int, BankTransaction> */
    public function findByBankAccount(int $bankAccountId): Collection;

    /** @return Collection<int, BankTransaction> */
    public function findByStatus(int $tenantId, string $status): Collection;

    /** @return Collection<int, BankTransaction> */
    public function findByDateRange(int $tenantId, string $startDate, string $endDate): Collection;

    public function create(array $data): BankTransaction;

    public function update(int $id, array $data): ?BankTransaction;

    /**
     * @param array<int> $ids
     */
    public function bulkUpdate(array $ids, array $data): int;
}
