<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Modules\Accounting\Domain\Entities\BankTransaction;

interface CategorizeTransactionServiceInterface
{
    public function categorize(int $bankTransactionId, int $accountId): BankTransaction;

    /**
     * Applies the highest-priority matching TransactionRule automatically.
     * Returns the updated transaction if a rule matched, null otherwise.
     */
    public function autoCategorize(int $bankTransactionId): ?BankTransaction;
}
