<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\BankAccount;

interface BankAccountServiceInterface
{
    public function findById(int $id): ?BankAccount;

    /** @return Collection<int, BankAccount> */
    public function findByTenant(int $tenantId): Collection;

    public function create(array $data): BankAccount;

    public function update(int $id, array $data): ?BankAccount;

    public function delete(int $id): bool;

    public function updateBalance(int $bankAccountId, float $newBalance): ?BankAccount;
}
