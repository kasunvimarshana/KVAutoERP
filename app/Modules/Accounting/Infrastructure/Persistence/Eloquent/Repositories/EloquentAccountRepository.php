<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\AccountModel;

class EloquentAccountRepository implements AccountRepositoryInterface
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

    public function findByType(int $tenantId, string $type): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Account
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Account
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

    public function all(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m))
            ->all();
    }

    public function getTree(int $tenantId): array
    {
        $accounts = $this->all($tenantId);

        return $this->buildTree($accounts, null);
    }

    private function buildTree(array $accounts, ?int $parentId): array
    {
        $nodes = [];

        foreach ($accounts as $account) {
            if ($account->getParentId() === $parentId) {
                $nodes[] = [
                    'account'  => $account,
                    'children' => $this->buildTree($accounts, $account->getId()),
                ];
            }
        }

        return $nodes;
    }

    private function toEntity(AccountModel $model): Account
    {
        return new Account(
            id: $model->id,
            tenantId: $model->tenant_id,
            code: $model->code,
            name: $model->name,
            type: $model->type,
            normalBalance: $model->normal_balance,
            parentId: $model->parent_id,
            isActive: (bool) $model->is_active,
            description: $model->description,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
