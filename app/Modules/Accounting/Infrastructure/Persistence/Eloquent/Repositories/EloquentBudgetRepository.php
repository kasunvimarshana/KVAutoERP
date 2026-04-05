<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\Budget;
use Modules\Accounting\Domain\RepositoryInterfaces\BudgetRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BudgetModel;

class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    public function __construct(
        private readonly BudgetModel $model,
    ) {}

    public function findById(int $id): ?Budget
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByAccount(int $tenantId, int $accountId, int $year, ?int $month = null): array
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('account_id', $accountId)
            ->where('year', $year);

        if ($tenantId !== 0) {
            $query->where('tenant_id', $tenantId);
        }

        if ($month !== null) {
            $query->where('month', $month);
        }

        return $query->get()
            ->map(fn (BudgetModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Budget
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Budget
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    private function toEntity(BudgetModel $model): Budget
    {
        return new Budget(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            accountId: $model->account_id,
            year: (int) $model->year,
            month: $model->month !== null ? (int) $model->month : null,
            amount: (float) $model->amount,
            spent: (float) $model->spent,
            notes: $model->notes,
            createdAt: $model->created_at,
        );
    }
}
