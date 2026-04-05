<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Modules\Accounting\Domain\Entities\BankTransaction;

interface CategorizeTransactionServiceInterface
{
    public function categorize(string $transactionId, string $categoryId, ?string $accountId = null): BankTransaction;
}
