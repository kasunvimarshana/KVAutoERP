<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\AccountModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentAccountRepository implements AccountRepositoryInterface
{
    public function __construct(private readonly AccountModel $model) {}

    public function findById(string $id): ?Account
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function findByCode(string $code, string $tenantId): ?Account
    {
        $m = $this->model->withoutGlobalScopes()
            ->where('code', $code)->where('tenant_id', $tenantId)->first();
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->orderBy('code')->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m));
    }

    public function getByType(string $type, string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->where('type', $type)->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m));
    }

    public function getRoots(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->whereNull('parent_id')->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m));
    }

    public function getChildren(string $parentId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('parent_id', $parentId)->get()
            ->map(fn (AccountModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Account
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(string $id, array $data): Account
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('Account', $id); }
        return (bool) $m->delete();
    }

    private function toEntity(AccountModel $m): Account
    {
        return new Account(
            id: $m->id, tenantId: $m->tenant_id, code: $m->code, name: $m->name,
            type: $m->type, parentId: $m->parent_id, isActive: (bool)$m->is_active,
            openingBalance: (float)$m->opening_balance, currentBalance: (float)$m->current_balance,
            currency: $m->currency ?? 'USD', description: $m->description,
        );
    }
}
