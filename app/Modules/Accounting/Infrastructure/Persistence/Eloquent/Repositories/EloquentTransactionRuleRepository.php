<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\TransactionRuleModel;

class EloquentTransactionRuleRepository implements TransactionRuleRepositoryInterface
{
    public function __construct(
        private readonly TransactionRuleModel $model,
    ) {}

    public function findActive(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('priority')
            ->get()
            ->map(fn (TransactionRuleModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): TransactionRule
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?TransactionRule
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

    private function toEntity(TransactionRuleModel $model): TransactionRule
    {
        return new TransactionRule(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            conditions: $model->conditions ?? [],
            applyTo: $model->apply_to,
            categoryId: $model->category_id,
            accountId: $model->account_id,
            priority: (int) $model->priority,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
