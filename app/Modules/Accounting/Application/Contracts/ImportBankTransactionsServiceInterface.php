<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

interface ImportBankTransactionsServiceInterface
{
    /**
     * Bulk-import bank transactions for the given bank account.
     *
     * @param array<int, array{date: string, amount: float, type: string, description: string, reference: ?string}> $transactions
     *
     * @return int Number of records inserted
     */
    public function import(int $bankAccountId, array $transactions): int;
}
