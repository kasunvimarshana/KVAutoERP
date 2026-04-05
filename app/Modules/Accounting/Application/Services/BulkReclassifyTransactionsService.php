<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\BulkReclassifyTransactionsServiceInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;

class BulkReclassifyTransactionsService implements BulkReclassifyTransactionsServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $repository,
    ) {}

    public function reclassify(array $transactionIds, string $categoryId): int
    {
        if (empty($transactionIds)) {
            throw new DomainException('At least one transaction ID is required.');
        }

        return $this->repository->bulkUpdateCategory($transactionIds, $categoryId);
    }
}
