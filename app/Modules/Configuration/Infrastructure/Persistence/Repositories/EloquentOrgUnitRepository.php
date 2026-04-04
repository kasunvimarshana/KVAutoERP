<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Models\OrgUnitClosureModel;
use Modules\Configuration\Infrastructure\Persistence\Models\OrgUnitModel;

class EloquentOrgUnitRepository implements OrgUnitRepositoryInterface
{
    public function __construct(
        private readonly OrgUnitModel $model,
        private readonly OrgUnitClosureModel $closureModel,
    ) {}

    public function findById(int $id): ?OrgUnit
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (OrgUnitModel $m) => $this->toEntity($m));
    }

    public function insertNode(array $data, ?int $parentId): OrgUnit
    {
        $model = $this->model->create(array_merge($data, ['parent_id' => $parentId]));

        // Insert self-reference closure row (depth=0)
        $this->closureModel->create([
            'ancestor_id'   => $model->id,
            'descendant_id' => $model->id,
            'depth'         => 0,
        ]);

        // Insert rows for all ancestors of the parent (depth+1)
        if ($parentId !== null) {
            $ancestorRows = $this->closureModel
                ->where('descendant_id', $parentId)
                ->get();

            foreach ($ancestorRows as $row) {
                $this->closureModel->create([
                    'ancestor_id'   => $row->ancestor_id,
                    'descendant_id' => $model->id,
                    'depth'         => $row->depth + 1,
                ]);
            }
        }

        return $this->toEntity($model);
    }

    public function updateNode(int $id, array $data): OrgUnit
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function deleteNode(int $id): bool
    {
        $model = $this->model->find($id);

        if (! $model) {
            return false;
        }

        // Delete all closure rows involving this node as a descendant
        $this->closureModel->where('descendant_id', $id)->delete();

        return (bool) $model->delete();
    }

    public function getDescendants(int $id): array
    {
        $descendantIds = $this->closureModel
            ->where('ancestor_id', $id)
            ->where('descendant_id', '!=', $id)
            ->orderBy('depth')
            ->pluck('descendant_id')
            ->toArray();

        return $this->model
            ->whereIn('id', $descendantIds)
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m))
            ->all();
    }

    public function getAncestors(int $id): array
    {
        $ancestorIds = $this->closureModel
            ->where('descendant_id', $id)
            ->where('ancestor_id', '!=', $id)
            ->orderByDesc('depth')
            ->pluck('ancestor_id')
            ->toArray();

        return $this->model
            ->whereIn('id', $ancestorIds)
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m))
            ->all();
    }

    public function move(int $id, ?int $newParentId): OrgUnit
    {
        $model = $this->model->findOrFail($id);

        // Step 1: Remove all closure rows where the subtree of $id is the descendant
        // and the ancestor is NOT in the subtree itself.
        $subtreeIds = $this->closureModel
            ->where('ancestor_id', $id)
            ->pluck('descendant_id')
            ->toArray();

        $this->closureModel
            ->whereIn('descendant_id', $subtreeIds)
            ->whereNotIn('ancestor_id', $subtreeIds)
            ->delete();

        // Step 2: Re-insert closure rows connecting new parent's ancestors to each subtree node.
        if ($newParentId !== null) {
            $parentAncestors = $this->closureModel
                ->where('descendant_id', $newParentId)
                ->get();

            $subtreeClosures = $this->closureModel
                ->where('ancestor_id', $id)
                ->get();

            foreach ($parentAncestors as $ancestor) {
                foreach ($subtreeClosures as $subtreeClosure) {
                    $this->closureModel->create([
                        'ancestor_id'   => $ancestor->ancestor_id,
                        'descendant_id' => $subtreeClosure->descendant_id,
                        'depth'         => $ancestor->depth + $subtreeClosure->depth + 1,
                    ]);
                }
            }
        }

        $model->update(['parent_id' => $newParentId]);

        return $this->toEntity($model->fresh());
    }

    public function getTree(int $tenantId): array
    {
        $allUnits = $this->model
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (OrgUnitModel $m) => $this->toEntity($m))
            ->all();

        return $this->buildTree($allUnits, null);
    }

    private function buildTree(array $units, ?int $parentId): array
    {
        $tree = [];

        foreach ($units as $unit) {
            if ($unit->parentId === $parentId) {
                $children = $this->buildTree($units, $unit->id);
                $node = (array) $unit;
                $node['children'] = $children;
                $tree[] = $node;
            }
        }

        return $tree;
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
            description: $model->description,
            isActive: (bool) $model->is_active,
            metadata: $model->metadata,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
        );
    }
}
