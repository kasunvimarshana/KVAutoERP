<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\TransactionRuleModel;

final class EloquentTransactionRuleRepository implements TransactionRuleRepositoryInterface
{
    public function __construct(
        private readonly TransactionRuleModel $model,
    ) {}

    public function findById(int $id): ?TransactionRule
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('priority')
            ->get()
            ->map(fn (TransactionRuleModel $m) => $this->toEntity($m));
    }

    public function findActive(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get()
            ->map(fn (TransactionRuleModel $m) => $this->toEntity($m));
    }

    public function create(array $data): TransactionRule
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?TransactionRule
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

    private function toEntity(TransactionRuleModel $model): TransactionRule
    {
        return new TransactionRule(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            conditions: (array) $model->conditions,
            accountId: $model->account_id,
            applyTo: $model->apply_to,
            priority: (int) $model->priority,
            isActive: (bool) $model->is_active,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
