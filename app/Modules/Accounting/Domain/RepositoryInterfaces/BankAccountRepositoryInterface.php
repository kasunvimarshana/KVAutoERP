<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\BankAccount;

interface BankAccountRepositoryInterface
{
    public function findById(string $id): ?BankAccount;
    public function allByTenant(string $tenantId): Collection;
    public function create(array $data): BankAccount;
    public function update(string $id, array $data): BankAccount;
    public function delete(string $id): bool;
    public function updateBalance(string $id, float $balance): BankAccount;
}
