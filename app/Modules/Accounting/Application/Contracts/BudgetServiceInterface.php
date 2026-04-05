<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Budget;

interface BudgetServiceInterface
{
    public function findById(int $id): ?Budget;

    /** @return Collection<int, Budget> */
    public function findByTenant(int $tenantId): Collection;

    public function create(array $data): Budget;

    public function update(int $id, array $data): ?Budget;

    public function delete(int $id): bool;

    public function updateSpent(int $budgetId, float $amount): ?Budget;

    public function getRemainingBudget(int $budgetId): float;
}
