<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

interface BulkReclassifyTransactionsServiceInterface
{
    /**
     * Reclassifies a set of bank transactions to the given chart-of-accounts entry.
     *
     * @param array<int> $transactionIds
     *
     * @return int Number of rows updated
     */
    public function reclassify(array $transactionIds, int $newAccountId): int;
}
