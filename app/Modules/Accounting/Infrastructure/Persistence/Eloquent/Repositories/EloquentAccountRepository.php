<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\AccountModel;

final class EloquentAccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        private readonly AccountModel $model,
    ) {}

    public function findById(int $id): ?Account
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, string $code): ?Account
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByType(int $tenantId, string $type): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->orderBy('code')
            ->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m));
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('code')
            ->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m));
    }

    public function getTree(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('code')
            ->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Account
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Account
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

    private function toEntity(AccountModel $model): Account
    {
        return new Account(
            id: $model->id,
            tenantId: $model->tenant_id,
            parentId: $model->parent_id,
            code: $model->code,
            name: $model->name,
            type: $model->type,
            normalBalance: $model->normal_balance,
            isActive: (bool) $model->is_active,
            description: $model->description,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
