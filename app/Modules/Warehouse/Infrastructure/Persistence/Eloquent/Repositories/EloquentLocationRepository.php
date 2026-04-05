<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Warehouse\Domain\Entities\Location;
use Modules\Warehouse\Domain\RepositoryInterfaces\LocationRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\LocationModel;

class EloquentLocationRepository implements LocationRepositoryInterface
{
    public function __construct(
        private readonly LocationModel $model,
    ) {}

    public function findById(int $id): ?Location
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, int $warehouseId, string $code): ?Location
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByWarehouse(int $warehouseId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $warehouseId)
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(fn (LocationModel $m) => $this->toEntity($m))
            ->all();
    }

    public function getTree(int $warehouseId): array
    {
        $all = $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $warehouseId)
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(fn (LocationModel $m) => $this->toEntity($m))
            ->all();

        return $this->buildTree($all, null);
    }

    public function getDescendants(int $locationId): array
    {
        $location = $this->model->newQueryWithoutScopes()->find($locationId);

        if ($location === null) {
            return [];
        }

        $pathPrefix = rtrim($location->path, '/') . '/' . $locationId . '/';

        return $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $location->warehouse_id)
            ->where('path', 'like', $pathPrefix . '%')
            ->get()
            ->map(fn (LocationModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Location
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Location
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

    /** @param Location[] $items */
    private function buildTree(array $items, ?int $parentId): array
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item->getParentId() === $parentId) {
                $children       = $this->buildTree($items, $item->getId());
                $tree[$item->getId()] = [
                    'location' => $item,
                    'children' => $children,
                ];
            }
        }

        return $tree;
    }

    private function toEntity(LocationModel $model): Location
    {
        return new Location(
            id: $model->id,
            tenantId: $model->tenant_id,
            warehouseId: $model->warehouse_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            parentId: $model->parent_id,
            path: $model->path ?? '/',
            level: (int) $model->level,
            capacity: $model->capacity !== null ? (float) $model->capacity : null,
            isActive: (bool) $model->is_active,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
