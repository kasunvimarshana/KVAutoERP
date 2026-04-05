<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\CategorizeTransactionServiceInterface;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class CategorizeTransactionService implements CategorizeTransactionServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $repository,
    ) {}

    public function categorize(string $transactionId, string $categoryId, ?string $accountId = null): BankTransaction
    {
        $transaction = $this->repository->findById($transactionId);

        if (! $transaction) {
            throw new NotFoundException('BankTransaction', $transactionId);
        }

        return $this->repository->updateCategory($transactionId, $categoryId, $accountId);
    }
}
