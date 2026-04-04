<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Models\WarehouseLocationClosureModel;
use Modules\Warehouse\Infrastructure\Persistence\Models\WarehouseLocationModel;

class EloquentWarehouseLocationRepository implements WarehouseLocationRepositoryInterface
{
    public function __construct(
        private readonly WarehouseLocationModel $model,
        private readonly WarehouseLocationClosureModel $closureModel,
    ) {}

    public function findById(int $id): ?WarehouseLocation
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByWarehouse(int $warehouseId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('warehouse_id', $warehouseId)
            ->orderBy('path')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (WarehouseLocationModel $m) => $this->toEntity($m));
    }

    public function insertNode(array $data, ?int $parentId): WarehouseLocation
    {
        $level = 0;
        $path = null;

        if ($parentId !== null) {
            $parent = $this->model->find($parentId);
            if ($parent) {
                $level = $parent->level + 1;
                $path = ($parent->path ? $parent->path . '/' : '') . $parent->id;
            }
        }

        $model = $this->model->create(array_merge($data, [
            'parent_id' => $parentId,
            'level'     => $level,
            'path'      => $path,
        ]));

        $this->closureModel->create([
            'ancestor_id'   => $model->id,
            'descendant_id' => $model->id,
            'depth'         => 0,
        ]);

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

    public function updateNode(int $id, array $data): WarehouseLocation
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

        $this->closureModel->where('descendant_id', $id)->delete();

        return (bool) $model->delete();
    }

    public function move(int $id, ?int $newParentId): WarehouseLocation
    {
        $model = $this->model->findOrFail($id);

        $subtreeIds = $this->closureModel
            ->where('ancestor_id', $id)
            ->pluck('descendant_id')
            ->toArray();

        $this->closureModel
            ->whereIn('descendant_id', $subtreeIds)
            ->whereNotIn('ancestor_id', $subtreeIds)
            ->delete();

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

        $level = 0;
        $path = null;
        if ($newParentId !== null) {
            $parent = $this->model->find($newParentId);
            if ($parent) {
                $level = $parent->level + 1;
                $path = ($parent->path ? $parent->path . '/' : '') . $parent->id;
            }
        }

        $model->update(['parent_id' => $newParentId, 'level' => $level, 'path' => $path]);

        return $this->toEntity($model->fresh());
    }

    public function getTree(int $warehouseId): array
    {
        $allLocations = $this->model
            ->where('warehouse_id', $warehouseId)
            ->orderBy('path')
            ->orderBy('name')
            ->get()
            ->map(fn (WarehouseLocationModel $m) => $this->toEntity($m))
            ->all();

        return $this->buildTree($allLocations, null);
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
            ->map(fn (WarehouseLocationModel $m) => $this->toEntity($m))
            ->all();
    }

    private function buildTree(array $locations, ?int $parentId): array
    {
        $tree = [];
        foreach ($locations as $location) {
            if ($location->parentId === $parentId) {
                $children = $this->buildTree($locations, $location->id);
                $node = (array) $location;
                $node['children'] = $children;
                $tree[] = $node;
            }
        }

        return $tree;
    }

    private function toEntity(WarehouseLocationModel $model): WarehouseLocation
    {
        return new WarehouseLocation(
            id: $model->id,
            tenantId: $model->tenant_id,
            warehouseId: $model->warehouse_id,
            parentId: $model->parent_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            barcode: $model->barcode,
            capacity: $model->capacity !== null ? (float) $model->capacity : null,
            isActive: (bool) $model->is_active,
            level: (int) $model->level,
            path: $model->path,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
        );
    }
}
