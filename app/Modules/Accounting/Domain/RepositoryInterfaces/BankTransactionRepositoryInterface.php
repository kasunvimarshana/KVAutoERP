<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\RepositoryInterfaces;
use Modules\Accounting\Domain\Entities\BankTransaction;
interface BankTransactionRepositoryInterface {
    public function findById(int $id): ?BankTransaction;
    public function findByBankAccount(int $bankAccountId): array;
    public function findUncategorized(int $tenantId): array;
    public function save(BankTransaction $txn): BankTransaction;
    public function saveMany(array $transactions): array;
}
