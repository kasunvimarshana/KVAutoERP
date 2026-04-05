<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\ImportBankTransactionsServiceInterface;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankAccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class ImportBankTransactionsService implements ImportBankTransactionsServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $transactionRepository,
        private readonly BankAccountRepositoryInterface $bankAccountRepository,
    ) {}

    public function import(int $bankAccountId, array $transactions): int
    {
        $bankAccount = $this->bankAccountRepository->findById($bankAccountId);

        if ($bankAccount === null) {
            throw new NotFoundException("Bank account #{$bankAccountId} not found.");
        }

        $count = 0;

        foreach ($transactions as $item) {
            $this->transactionRepository->create([
                'tenant_id'       => $bankAccount->tenantId,
                'bank_account_id' => $bankAccountId,
                'date'            => $item['date'],
                'amount'          => (float) $item['amount'],
                'type'            => $item['type'],
                'description'     => $item['description'],
                'reference'       => $item['reference'] ?? null,
                'source'          => BankTransaction::SOURCE_IMPORT,
                'status'          => BankTransaction::STATUS_PENDING,
            ]);

            ++$count;
        }

        return $count;
    }
}
