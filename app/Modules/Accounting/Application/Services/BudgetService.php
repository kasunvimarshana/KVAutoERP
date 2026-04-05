<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\BudgetServiceInterface;
use Modules\Accounting\Domain\Entities\Budget;
use Modules\Accounting\Domain\RepositoryInterfaces\BudgetRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class BudgetService implements BudgetServiceInterface
{
    public function __construct(
        private readonly BudgetRepositoryInterface $repository,
    ) {}

    public function findById(int $id): ?Budget
    {
        return $this->repository->findById($id);
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->repository->findByTenant($tenantId);
    }

    public function create(array $data): Budget
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?Budget
    {
        $budget = $this->repository->findById($id);

        if ($budget === null) {
            throw new NotFoundException("Budget #{$id} not found.");
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $budget = $this->repository->findById($id);

        if ($budget === null) {
            throw new NotFoundException("Budget #{$id} not found.");
        }

        return $this->repository->delete($id);
    }

    public function updateSpent(int $budgetId, float $amount): ?Budget
    {
        $budget = $this->repository->findById($budgetId);

        if ($budget === null) {
            throw new NotFoundException("Budget #{$budgetId} not found.");
        }

        return $this->repository->update($budgetId, ['spent' => $amount]);
    }

    public function getRemainingBudget(int $budgetId): float
    {
        $budget = $this->repository->findById($budgetId);

        if ($budget === null) {
            throw new NotFoundException("Budget #{$budgetId} not found.");
        }

        return $budget->getRemaining();
    }
}
