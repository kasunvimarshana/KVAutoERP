<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\ImportBankTransactionsServiceInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;

class ImportBankTransactionsService implements ImportBankTransactionsServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $transactionRepository,
    ) {}

    public function import(int $bankAccountId, array $transactions): int
    {
        $count = 0;

        foreach ($transactions as $tx) {
            $this->transactionRepository->create(array_merge($tx, [
                'bank_account_id' => $bankAccountId,
                'status'          => $tx['status'] ?? 'pending',
                'source'          => $tx['source'] ?? 'import',
            ]));
            ++$count;
        }

        return $count;
    }
}
