<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\BudgetServiceInterface;
use Modules\Accounting\Domain\Entities\Budget;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BudgetRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class BudgetService implements BudgetServiceInterface
{
    public function __construct(
        private readonly BudgetRepositoryInterface $repository,
        private readonly AccountRepositoryInterface $accountRepository,
    ) {}

    public function createBudget(array $data): Budget
    {
        return $this->repository->create($data);
    }

    public function updateBudget(string $id, array $data): Budget
    {
        $this->getBudget($id);
        return $this->repository->update($id, $data);
    }

    public function deleteBudget(string $id): bool
    {
        $this->getBudget($id);
        return $this->repository->delete($id);
    }

    public function getBudget(string $id): Budget
    {
        $budget = $this->repository->findById($id);
        if (! $budget) {
            throw new NotFoundException('Budget', $id);
        }
        return $budget;
    }

    public function getAll(string $tenantId): Collection
    {
        return $this->repository->allByTenant($tenantId);
    }

    public function getBudgetVsActual(string $budgetId): array
    {
        $budget = $this->getBudget($budgetId);
        $account = $this->accountRepository->findById($budget->getAccountId());

        $actual = $account ? $account->getCurrentBalance() : 0.0;
        $budgeted = $budget->getAmount();
        $variance = $budgeted - $actual;

        return [
            'budget_id'       => $budget->getId(),
            'budget_name'     => $budget->getName(),
            'account_id'      => $budget->getAccountId(),
            'account_name'    => $account?->getName(),
            'fiscal_year'     => $budget->getFiscalYear(),
            'period'          => $budget->getPeriod(),
            'budgeted_amount' => $budgeted,
            'actual_amount'   => $actual,
            'variance'        => $variance,
            'variance_pct'    => $budgeted != 0 ? round(($variance / $budgeted) * 100, 2) : null,
            'on_budget'       => $actual <= $budgeted,
        ];
    }
}
