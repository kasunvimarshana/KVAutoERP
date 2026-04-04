<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Facades\DB;
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
        $model = $this->model->newQuery()->withoutGlobalScopes()->find($id);

        return $model ? $this->mapModelToEntity($model) : null;
    }

    public function findAllByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->mapModelToEntity($m))
            ->all();
    }

    public function findChildren(int $parentId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScopes()
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->mapModelToEntity($m))
            ->all();
    }

    public function findDescendants(int $ancestorId): array
    {
        $descendantIds = DB::table('org_unit_closures')
            ->where('ancestor_id', $ancestorId)
            ->where('depth', '>', 0)
            ->pluck('descendant_id')
            ->all();

        if (empty($descendantIds)) {
            return [];
        }

        return $this->model->newQuery()
            ->withoutGlobalScopes()
            ->whereIn('id', $descendantIds)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->mapModelToEntity($m))
            ->all();
    }

    public function save(OrgUnit $orgUnit): OrgUnit
    {
        if ($orgUnit->id === null) {
            return $this->create($orgUnit);
        }

        return $this->update($orgUnit);
    }

    public function delete(int $id): void
    {
        DB::table('org_unit_closures')
            ->where('ancestor_id', $id)
            ->orWhere('descendant_id', $id)
            ->delete();

        $this->model->newQuery()->withoutGlobalScopes()->findOrFail($id)->delete();
    }

    public function buildTree(int $tenantId): array
    {
        $all = $this->findAllByTenant($tenantId);

        $indexed = [];
        foreach ($all as $unit) {
            $indexed[$unit->id] = $unit;
        }

        $roots = [];
        foreach ($indexed as $unit) {
            if ($unit->parentId === null || !isset($indexed[$unit->parentId])) {
                $roots[] = $unit;
            } else {
                $indexed[$unit->parentId]->children[] = $unit;
            }
        }

        return $roots;
    }

    private function create(OrgUnit $orgUnit): OrgUnit
    {
        $model = $this->model->newQuery()->create([
            'tenant_id'   => $orgUnit->tenantId,
            'parent_id'   => $orgUnit->parentId,
            'name'        => $orgUnit->name,
            'code'        => $orgUnit->code,
            'type'        => $orgUnit->type,
            'description' => $orgUnit->description,
            'is_active'   => $orgUnit->isActive,
            'sort_order'  => $orgUnit->sortOrder,
        ]);

        $newId = (int) $model->id;

        // Self-reference row
        DB::table('org_unit_closures')->insert([
            'ancestor_id'   => $newId,
            'descendant_id' => $newId,
            'depth'         => 0,
        ]);

        // Ancestor rows from parent's closure paths
        if ($orgUnit->parentId !== null) {
            $ancestorRows = DB::table('org_unit_closures')
                ->where('descendant_id', $orgUnit->parentId)
                ->get(['ancestor_id', 'depth']);

            foreach ($ancestorRows as $row) {
                DB::table('org_unit_closures')->insert([
                    'ancestor_id'   => $row->ancestor_id,
                    'descendant_id' => $newId,
                    'depth'         => $row->depth + 1,
                ]);
            }
        }

        return $this->mapModelToEntity($model);
    }

    private function update(OrgUnit $orgUnit): OrgUnit
    {
        $model = $this->model->newQuery()->withoutGlobalScopes()->findOrFail($orgUnit->id);

        $parentChanged = $model->parent_id !== $orgUnit->parentId;

        $model->update([
            'tenant_id'   => $orgUnit->tenantId,
            'parent_id'   => $orgUnit->parentId,
            'name'        => $orgUnit->name,
            'code'        => $orgUnit->code,
            'type'        => $orgUnit->type,
            'description' => $orgUnit->description,
            'is_active'   => $orgUnit->isActive,
            'sort_order'  => $orgUnit->sortOrder,
        ]);

        if ($parentChanged) {
            $this->rebuildClosurePaths((int) $orgUnit->id, $orgUnit->parentId);
        }

        $model->refresh();

        return $this->mapModelToEntity($model);
    }

    private function rebuildClosurePaths(int $nodeId, ?int $newParentId): void
    {
        // Collect all descendants of this node (including self)
        $subtreeIds = DB::table('org_unit_closures')
            ->where('ancestor_id', $nodeId)
            ->pluck('descendant_id', 'descendant_id')
            ->all();

        // Remove all closure rows where ancestor is outside the subtree
        // and descendant is inside the subtree (i.e., the old paths from ancestors above)
        DB::table('org_unit_closures')
            ->whereNotIn('ancestor_id', array_keys($subtreeIds))
            ->whereIn('descendant_id', array_keys($subtreeIds))
            ->delete();

        // Re-insert paths from the new parent's ancestors into every subtree node
        if ($newParentId !== null) {
            $ancestorRows = DB::table('org_unit_closures')
                ->where('descendant_id', $newParentId)
                ->get(['ancestor_id', 'depth']);

            $subtreeDepths = DB::table('org_unit_closures')
                ->where('ancestor_id', $nodeId)
                ->get(['descendant_id', 'depth']);

            $inserts = [];
            foreach ($ancestorRows as $ancestor) {
                foreach ($subtreeDepths as $sub) {
                    $inserts[] = [
                        'ancestor_id'   => $ancestor->ancestor_id,
                        'descendant_id' => $sub->descendant_id,
                        'depth'         => $ancestor->depth + $sub->depth + 1,
                    ];
                }
            }

            if (!empty($inserts)) {
                DB::table('org_unit_closures')->insertOrIgnore($inserts);
            }
        }
    }

    private function mapModelToEntity(OrgUnitModel $model): OrgUnit
    {
        return new OrgUnit(
            id: $model->id,
            tenantId: $model->tenant_id,
            parentId: $model->parent_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            description: $model->description,
            isActive: (bool) $model->is_active,
            sortOrder: (int) $model->sort_order,
            children: [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
