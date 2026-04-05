<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounting\Application\Contracts\ImportBankTransactionsServiceInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;

class ImportBankTransactionsService implements ImportBankTransactionsServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $repository,
    ) {}

    public function import(string $bankAccountId, array $transactions): int
    {
        if (empty($transactions)) {
            return 0;
        }

        $now = now()->toDateTimeString();
        $records = array_map(function (array $tx) use ($bankAccountId, $now) {
            return [
                'id'              => (string) Str::uuid(),
                'tenant_id'       => $tx['tenant_id'],
                'bank_account_id' => $bankAccountId,
                'date'            => $tx['date'],
                'description'     => $tx['description'],
                'amount'          => $tx['amount'],
                'type'            => $tx['type'] ?? ($tx['amount'] >= 0 ? 'credit' : 'debit'),
                'status'          => 'pending',
                'source'          => $tx['source'] ?? 'import',
                'reference'       => $tx['reference'] ?? null,
                'metadata'        => isset($tx['metadata']) ? json_encode($tx['metadata']) : null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }, $transactions);

        return DB::transaction(fn () => $this->repository->bulkInsert($records));
    }
}
