<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Budget;

interface BudgetServiceInterface
{
    public function createBudget(array $data): Budget;
    public function updateBudget(string $id, array $data): Budget;
    public function deleteBudget(string $id): bool;
    public function getBudget(string $id): Budget;
    public function getAll(string $tenantId): Collection;
    public function getBudgetVsActual(string $budgetId): array;
}
