<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Budget;

interface BudgetRepositoryInterface
{
    public function findById(int $id): ?Budget;

    /** @return Collection<int, Budget> */
    public function findByTenant(int $tenantId): Collection;

    /** @return Collection<int, Budget> */
    public function findByAccount(int $tenantId, int $accountId): Collection;

    /**
     * Returns budgets whose period overlaps the given date range.
     *
     * @return Collection<int, Budget>
     */
    public function findByPeriod(int $tenantId, string $startDate, string $endDate): Collection;

    public function create(array $data): Budget;

    public function update(int $id, array $data): ?Budget;

    public function delete(int $id): bool;
}
