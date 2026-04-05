<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\TransactionRuleModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentTransactionRuleRepository implements TransactionRuleRepositoryInterface
{
    public function __construct(private readonly TransactionRuleModel $model) {}

    public function findById(string $id): ?TransactionRule
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->orderBy('priority')->get()
            ->map(fn (TransactionRuleModel $m) => $this->toEntity($m));
    }

    public function getActive(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->where('is_active', true)->orderBy('priority')->get()
            ->map(fn (TransactionRuleModel $m) => $this->toEntity($m));
    }

    public function create(array $data): TransactionRule
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(string $id, array $data): TransactionRule
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('TransactionRule', $id); }
        return (bool) $m->delete();
    }

    private function toEntity(TransactionRuleModel $m): TransactionRule
    {
        return new TransactionRule(
            id: $m->id, tenantId: $m->tenant_id, name: $m->name,
            conditions: $m->conditions ?? [], categoryId: $m->category_id,
            accountId: $m->account_id, applyTo: $m->apply_to ?? 'all',
            priority: (int)$m->priority, isActive: (bool)$m->is_active,
        );
    }
}
