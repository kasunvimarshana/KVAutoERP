<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Budget;
use Modules\Accounting\Domain\RepositoryInterfaces\BudgetRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BudgetModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    public function __construct(private readonly BudgetModel $model) {}

    public function findById(string $id): ?Budget
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->get()
            ->map(fn (BudgetModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Budget
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(string $id, array $data): Budget
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('Budget', $id); }
        return (bool) $m->delete();
    }

    private function toEntity(BudgetModel $m): Budget
    {
        return new Budget(
            id: $m->id, tenantId: $m->tenant_id, name: $m->name,
            fiscalYear: (int)$m->fiscal_year, accountId: $m->account_id,
            amount: (float)$m->amount, period: $m->period,
            startDate: new \DateTimeImmutable($m->start_date->toDateString()),
            endDate: new \DateTimeImmutable($m->end_date->toDateString()),
            notes: $m->notes,
        );
    }
}
