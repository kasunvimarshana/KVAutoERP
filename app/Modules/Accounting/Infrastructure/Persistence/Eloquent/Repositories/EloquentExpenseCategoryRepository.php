<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\ExpenseCategory;
use Modules\Accounting\Domain\RepositoryInterfaces\ExpenseCategoryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\ExpenseCategoryModel;

final class EloquentExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function __construct(
        private readonly ExpenseCategoryModel $model,
    ) {}

    public function findById(int $id): ?ExpenseCategory
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (ExpenseCategoryModel $m) => $this->toEntity($m));
    }

    public function findByAccount(int $tenantId, int $accountId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('account_id', $accountId)
            ->orderBy('name')
            ->get()
            ->map(fn (ExpenseCategoryModel $m) => $this->toEntity($m));
    }

    public function create(array $data): ExpenseCategory
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ExpenseCategory
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

    private function toEntity(ExpenseCategoryModel $model): ExpenseCategory
    {
        return new ExpenseCategory(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            accountId: $model->account_id,
            parentId: $model->parent_id,
            color: $model->color,
            isActive: (bool) $model->is_active,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
