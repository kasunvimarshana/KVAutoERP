<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Modules\Accounting\Domain\Entities\BankTransaction;

interface BankTransactionRepositoryInterface
{
    public function findById(int $id): ?BankTransaction;

    /** @return BankTransaction[] */
    public function findByBankAccount(int $bankAccountId): array;

    /** @return BankTransaction[] */
    public function findByStatus(int $tenantId, string $status): array;

    public function create(array $data): BankTransaction;

    public function update(int $id, array $data): ?BankTransaction;

    public function delete(int $id): bool;

    public function bulkUpdateStatus(array $ids, string $status): int;
}
