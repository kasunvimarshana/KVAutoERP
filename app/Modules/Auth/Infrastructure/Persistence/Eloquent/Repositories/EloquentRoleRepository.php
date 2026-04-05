<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\RoleModel;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private readonly RoleModel $model,
    ) {}

    public function findById(int $id): ?Role
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findBySlug(string $slug, ?int $tenantId = null): ?Role
    {
        $query = $this->model->newQueryWithoutScopes()->where('slug', $slug);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $record = $query->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): Role
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Role
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

    public function allForTenant(?int $tenantId): array
    {
        $query = $this->model->newQueryWithoutScopes();

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query
            ->get()
            ->map(fn (RoleModel $m) => $this->toEntity($m))
            ->all();
    }

    public function syncPermissions(int $roleId, array $permissions): void
    {
        $record = $this->model->newQueryWithoutScopes()->find($roleId);

        if ($record !== null) {
            $record->permissions = $permissions;
            $record->save();
        }
    }

    private function toEntity(RoleModel $model): Role
    {
        return new Role(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            slug: $model->slug,
            permissions: $model->permissions,
            description: $model->description,
            createdAt: $model->created_at,
        );
    }
}
