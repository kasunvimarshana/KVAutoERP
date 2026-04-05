<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\ExpenseCategory;

interface ExpenseCategoryRepositoryInterface
{
    public function findById(int $id): ?ExpenseCategory;

    /** @return Collection<int, ExpenseCategory> */
    public function findByTenant(int $tenantId): Collection;

    /** @return Collection<int, ExpenseCategory> */
    public function findByAccount(int $tenantId, int $accountId): Collection;

    public function create(array $data): ExpenseCategory;

    public function update(int $id, array $data): ?ExpenseCategory;

    public function delete(int $id): bool;
}
