<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Modules\Accounting\Domain\Entities\Budget;

interface BudgetServiceInterface
{
    public function findById(int $id): Budget;

    public function create(array $data): Budget;

    public function update(int $id, array $data): Budget;

    /**
     * @return array{budget: float, spent: float, variance: float}
     */
    public function getVariance(int $accountId, int $year, ?int $month = null): array;
}
