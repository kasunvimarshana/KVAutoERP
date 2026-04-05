<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\OrgUnitModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentOrgUnitRepository implements OrgUnitRepositoryInterface
{
    public function __construct(private readonly OrgUnitModel $model) {}

    public function findById(string $id): ?OrgUnit
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByCode(string $code, string $tenantId): ?OrgUnit
    {
        $model = $this->model->withoutGlobalScopes()
            ->where('code', $code)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function getTree(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function getDescendants(string $id): Collection
    {
        $unit = $this->model->withoutGlobalScopes()->findOrFail($id);

        return $this->model->withoutGlobalScopes()
            ->where('path', 'LIKE', rtrim($unit->path, '/').'/%')
            ->orderBy('level')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function getAncestors(string $id): Collection
    {
        $unit = $this->model->withoutGlobalScopes()->findOrFail($id);

        // Extract ancestor IDs from the materialized path (e.g. "root/parent/self")
        $segments = array_filter(explode('/', trim($unit->path, '/')));
        array_pop($segments); // remove self

        if (empty($segments)) {
            return collect();
        }

        return $this->model->withoutGlobalScopes()
            ->whereIn('id', $segments)
            ->orderBy('level')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function create(array $data): OrgUnit
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(string $id, array $data): OrgUnit
    {
        $model = $this->model->withoutGlobalScopes()->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(string $id): bool
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        if (! $model) {
            throw new NotFoundException('OrgUnit', $id);
        }

        return (bool) $model->delete();
    }

    public function move(string $id, ?string $newParentId): OrgUnit
    {
        $model = $this->model->withoutGlobalScopes()->findOrFail($id);
        $oldPath = $model->path;

        if ($newParentId) {
            $parent = $this->model->withoutGlobalScopes()->findOrFail($newParentId);
            $newPath = rtrim($parent->path, '/').'/'.$id.'/';
            $newLevel = $parent->level + 1;
        } else {
            $newPath = '/'.$id.'/';
            $newLevel = 0;
        }

        // Update descendant paths
        $descendants = $this->model->withoutGlobalScopes()
            ->where('path', 'LIKE', rtrim($oldPath, '/').'/%')
            ->get();

        foreach ($descendants as $descendant) {
            $descendant->path = str_replace($oldPath, $newPath, $descendant->path);
            $descendant->level = $descendant->level - $model->level + $newLevel;
            $descendant->save();
        }

        $model->update([
            'parent_id' => $newParentId,
            'path'      => $newPath,
            'level'     => $newLevel,
        ]);

        return $this->toEntity($model->fresh());
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
            metadata: $model->metadata ?? [],
        );
    }
}
