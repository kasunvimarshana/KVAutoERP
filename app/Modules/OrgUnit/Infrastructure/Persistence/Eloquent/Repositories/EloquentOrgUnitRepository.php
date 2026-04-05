<?php

declare(strict_types=1);

namespace Modules\OrgUnit\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\OrgUnit\Domain\Entities\OrgUnit;
use Modules\OrgUnit\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\OrgUnit\Infrastructure\Persistence\Eloquent\Models\OrgUnitModel;

final class EloquentOrgUnitRepository implements OrgUnitRepositoryInterface
{
    public function __construct(
        private readonly OrgUnitModel $model,
    ) {}

    public function findById(int $id): ?OrgUnit
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, string $code): ?OrgUnit
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('path')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function create(array $data): OrgUnit
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?OrgUnit
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

    public function getTree(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('path')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function getDescendants(int $orgUnitId): Collection
    {
        $unit = $this->model->newQueryWithoutScopes()->find($orgUnitId);

        if ($unit === null) {
            return new Collection();
        }

        $prefix = $unit->path . $unit->id . '/';

        return $this->model->newQueryWithoutScopes()
            ->where('path', 'like', $prefix . '%')
            ->orderBy('path')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function getAncestors(int $orgUnitId): Collection
    {
        $unit = $this->model->newQueryWithoutScopes()->find($orgUnitId);

        if ($unit === null) {
            return new Collection();
        }

        // Path format: '/1/5/12/' — extract ancestor IDs by splitting on '/'
        $ancestorIds = array_filter(explode('/', $unit->path));

        if (empty($ancestorIds)) {
            return new Collection();
        }

        return $this->model->newQueryWithoutScopes()
            ->whereIn('id', $ancestorIds)
            ->orderBy('level')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function move(int $orgUnitId, ?int $newParentId): OrgUnit
    {
        return DB::transaction(function () use ($orgUnitId, $newParentId): OrgUnit {
            $unit = $this->model->newQueryWithoutScopes()->find($orgUnitId);

            if ($newParentId !== null) {
                $newParent   = $this->model->newQueryWithoutScopes()->find($newParentId);
                $newPath     = $newParent->path . $newParent->id . '/';
                $newLevel    = $newParent->level + 1;
            } else {
                $newPath  = '/';
                $newLevel = 0;
            }

            $oldPrefix = $unit->path . $unit->id . '/';
            $newPrefix = $newPath . $unit->id . '/';
            $levelDiff = $newLevel - $unit->level;

            // Update all descendants first (path and level)
            $descendants = $this->model->newQueryWithoutScopes()
                ->where('path', 'like', $oldPrefix . '%')
                ->get();

            foreach ($descendants as $descendant) {
                $updatedPath = $newPrefix . substr($descendant->path, strlen($oldPrefix));
                $descendant->update([
                    'path'  => $updatedPath,
                    'level' => $descendant->level + $levelDiff,
                ]);
            }

            // Update the unit itself
            $unit->update([
                'parent_id' => $newParentId,
                'path'      => $newPath,
                'level'     => $newLevel,
            ]);

            return $this->toEntity($unit->fresh());
        });
    }

    private function toEntity(OrgUnitModel $model): OrgUnit
    {
        return new OrgUnit(
            id: $model->id,
            tenantId: $model->tenant_id,
            parentId: $model->parent_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            path: $model->path,
            level: (int) $model->level,
            isActive: (bool) $model->is_active,
            metadata: $model->metadata,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
