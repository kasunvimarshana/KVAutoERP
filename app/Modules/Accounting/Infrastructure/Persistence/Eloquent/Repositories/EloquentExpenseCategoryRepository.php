<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\ExpenseCategory;
use Modules\Accounting\Domain\RepositoryInterfaces\ExpenseCategoryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\ExpenseCategoryModel;

class EloquentExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function __construct(
        private readonly ExpenseCategoryModel $model,
    ) {}

    public function findById(int $id): ?ExpenseCategory
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenantId(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (ExpenseCategoryModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): ExpenseCategory
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ExpenseCategory
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

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
            code: $model->code,
            parentId: $model->parent_id,
            accountId: $model->account_id,
            color: $model->color,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
