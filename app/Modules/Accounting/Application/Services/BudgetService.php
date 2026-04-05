<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\BudgetServiceInterface;
use Modules\Accounting\Domain\Entities\Budget;
use Modules\Accounting\Domain\RepositoryInterfaces\BudgetRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class BudgetService implements BudgetServiceInterface
{
    public function __construct(
        private readonly BudgetRepositoryInterface $repository,
    ) {}

    public function findById(int $id): Budget
    {
        $budget = $this->repository->findById($id);

        if ($budget === null) {
            throw new NotFoundException('Budget', $id);
        }

        return $budget;
    }

    public function create(array $data): Budget
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Budget
    {
        $budget = $this->repository->update($id, $data);

        if ($budget === null) {
            throw new NotFoundException('Budget', $id);
        }

        return $budget;
    }

    public function getVariance(int $accountId, int $year, ?int $month = null): array
    {
        // Use a sentinel tenantId; caller typically scopes by tenantId prior to this call.
        // Here we retrieve budgets for the given account/year/month across all matching records.
        $budgets = $this->repository->findByAccount(0, $accountId, $year, $month);

        // Aggregate across multiple budget records if they exist
        $totalBudget = 0.0;
        $totalSpent  = 0.0;

        foreach ($budgets as $budget) {
            $totalBudget += $budget->getAmount();
            $totalSpent  += $budget->getSpent();
        }

        return [
            'budget'   => $totalBudget,
            'spent'    => $totalSpent,
            'variance' => $totalBudget - $totalSpent,
        ];
    }
}
