<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\OrgUnitModel;

class EloquentOrgUnitRepository implements OrgUnitRepositoryInterface
{
    public function __construct(
        private readonly OrgUnitModel $model,
    ) {}

    public function findById(int $id): ?OrgUnit
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenantId(int $tenantId): array
    {
        return $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): OrgUnit
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?OrgUnit
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

    public function getTree(int $tenantId): array
    {
        return $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('path')
            ->orderBy('level')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m))
            ->all();
    }

    public function getDescendants(int $id): array
    {
        $unit = $this->model->newQueryWithoutScopes()->find($id);

        if ($unit === null) {
            return [];
        }

        $prefix = $unit->path . $id . '/';

        return $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $unit->tenant_id)
            ->where('path', 'like', $prefix . '%')
            ->orderBy('level')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m))
            ->all();
    }

    public function getDescendantIds(int $id): array
    {
        $unit = $this->model->newQueryWithoutScopes()->find($id);

        if ($unit === null) {
            return [];
        }

        $prefix = $unit->path . $id . '/';

        return $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $unit->tenant_id)
            ->where('path', 'like', $prefix . '%')
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public function getAncestors(int $id): array
    {
        $unit = $this->model->newQueryWithoutScopes()->find($id);

        if ($unit === null) {
            return [];
        }

        // path is like "/1/5/" — extract segment IDs
        $ancestorIds = array_filter(
            explode('/', trim($unit->path, '/')),
            fn (string $segment) => $segment !== '',
        );

        if (empty($ancestorIds)) {
            return [];
        }

        return $this->model
            ->newQueryWithoutScopes()
            ->whereIn('id', array_map('intval', array_values($ancestorIds)))
            ->orderBy('level')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(OrgUnitModel $model): OrgUnit
    {
        return new OrgUnit(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            parentId: $model->parent_id,
            path: $model->path,
            level: $model->level,
            isActive: (bool) $model->is_active,
            description: $model->description,
            metadata: $model->metadata,
            createdAt: $model->created_at,
        );
    }
}
