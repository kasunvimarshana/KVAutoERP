<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\BulkReclassifyTransactionsServiceInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;

class BulkReclassifyTransactionsService implements BulkReclassifyTransactionsServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $transactionRepository,
    ) {}

    public function reclassify(array $transactionIds, ?int $categoryId, ?int $accountId): int
    {
        if (empty($transactionIds)) {
            return 0;
        }

        $count = 0;

        foreach ($transactionIds as $id) {
            $updateData = ['status' => 'categorized'];

            if ($categoryId !== null) {
                $updateData['category'] = (string) $categoryId;
            }

            if ($accountId !== null) {
                $updateData['account_id'] = $accountId;
            }

            $result = $this->transactionRepository->update((int) $id, $updateData);

            if ($result !== null) {
                ++$count;
            }
        }

        return $count;
    }
}
