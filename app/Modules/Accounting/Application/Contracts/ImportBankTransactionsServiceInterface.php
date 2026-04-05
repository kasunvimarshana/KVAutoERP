<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

interface ImportBankTransactionsServiceInterface
{
    public function import(int $bankAccountId, array $transactions): int;
}
