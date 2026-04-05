<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\BulkReclassifyTransactionsServiceInterface;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;

final class BulkReclassifyTransactionsService implements BulkReclassifyTransactionsServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $transactionRepository,
    ) {}

    public function reclassify(array $transactionIds, int $newAccountId): int
    {
        if (empty($transactionIds)) {
            return 0;
        }

        return $this->transactionRepository->bulkUpdate($transactionIds, [
            'account_id' => $newAccountId,
            'status'     => BankTransaction::STATUS_CATEGORIZED,
        ]);
    }
}
