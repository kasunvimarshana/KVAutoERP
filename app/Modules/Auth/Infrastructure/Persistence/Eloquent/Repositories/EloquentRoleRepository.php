<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(private readonly RoleModel $model) {}

    public function findById(string $id): ?Role
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug, string $tenantId): ?Role
    {
        $model = $this->model->withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (RoleModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Role
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(string $id, array $data): Role
    {
        $model = $this->model->withoutGlobalScopes()->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(string $id): bool
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        if (! $model) {
            throw new NotFoundException('Role', $id);
        }

        return (bool) $model->delete();
    }

    private function toEntity(RoleModel $model): Role
    {
        return new Role(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            permissions: $model->permissions ?? [],
            tenantId: $model->tenant_id,
        );
    }
}
