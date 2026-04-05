<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Budget;
use Modules\Accounting\Domain\RepositoryInterfaces\BudgetRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BudgetModel;

final class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    public function __construct(
        private readonly BudgetModel $model,
    ) {}

    public function findById(int $id): ?Budget
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn (BudgetModel $m) => $this->toEntity($m));
    }

    public function findByAccount(int $tenantId, int $accountId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('account_id', $accountId)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn (BudgetModel $m) => $this->toEntity($m));
    }

    public function findByPeriod(int $tenantId, string $startDate, string $endDate): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->orderBy('start_date')
            ->get()
            ->map(fn (BudgetModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Budget
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Budget
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function toEntity(BudgetModel $model): Budget
    {
        return new Budget(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            accountId: $model->account_id,
            periodType: $model->period_type,
            startDate: \DateTimeImmutable::createFromMutable($model->start_date->toDateTime()),
            endDate: \DateTimeImmutable::createFromMutable($model->end_date->toDateTime()),
            amount: (float) $model->amount,
            spent: (float) $model->spent,
            notes: $model->notes,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
