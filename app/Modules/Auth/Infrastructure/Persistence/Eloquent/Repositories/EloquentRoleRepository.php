<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\RoleModel;

final class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private readonly RoleModel $model,
    ) {}

    public function findById(int $id): ?Role
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByName(string $name, string $guardName = 'api', ?int $tenantId = null): ?Role
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('name', $name)
            ->where('guard_name', $guardName);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $record = $query->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenantId(?int $tenantId): Collection
    {
        $query = $this->model->newQueryWithoutScopes();

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->whereNull('tenant_id');
        }

        return $query->get()->map(fn (RoleModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Role
    {
        $record = $this->model->newInstance();
        $record->forceFill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Role
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

    private function toEntity(RoleModel $model): Role
    {
        return new Role(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            guardName: $model->guard_name,
            permissions: $model->permissions,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
