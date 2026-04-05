<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Modules\Accounting\Domain\Entities\Budget;

interface BudgetRepositoryInterface
{
    public function findById(int $id): ?Budget;

    /** @return Budget[] */
    public function findByAccount(int $tenantId, int $accountId, int $year, ?int $month = null): array;

    public function create(array $data): Budget;

    public function update(int $id, array $data): ?Budget;
}
