<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\ExpenseCategory;
use Modules\Accounting\Domain\RepositoryInterfaces\ExpenseCategoryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\ExpenseCategoryModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function __construct(private readonly ExpenseCategoryModel $model) {}

    public function findById(string $id): ?ExpenseCategory
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function findByCode(string $code, string $tenantId): ?ExpenseCategory
    {
        $m = $this->model->withoutGlobalScopes()
            ->where('code', $code)->where('tenant_id', $tenantId)->first();
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->get()
            ->map(fn (ExpenseCategoryModel $m) => $this->toEntity($m));
    }

    public function create(array $data): ExpenseCategory
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(string $id, array $data): ExpenseCategory
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('ExpenseCategory', $id); }
        return (bool) $m->delete();
    }

    private function toEntity(ExpenseCategoryModel $m): ExpenseCategory
    {
        return new ExpenseCategory(
            id: $m->id, tenantId: $m->tenant_id, name: $m->name, code: $m->code,
            parentId: $m->parent_id, accountId: $m->account_id,
            isActive: (bool)$m->is_active, description: $m->description,
        );
    }
}
