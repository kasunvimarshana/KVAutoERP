<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\WarehouseLocation;
use Modules\Inventory\Domain\RepositoryInterfaces\WarehouseLocationRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\WarehouseLocationModel;

final class EloquentWarehouseLocationRepository implements WarehouseLocationRepositoryInterface
{
    public function __construct(
        private readonly WarehouseLocationModel $model,
    ) {}

    public function findById(int $id): ?WarehouseLocation
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByWarehouse(int $warehouseId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $warehouseId)
            ->orderBy('path')
            ->get()
            ->map(fn (WarehouseLocationModel $m) => $this->toEntity($m));
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('path')
            ->get()
            ->map(fn (WarehouseLocationModel $m) => $this->toEntity($m));
    }

    public function getTree(int $warehouseId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('warehouse_id', $warehouseId)
            ->orderBy('path')
            ->get()
            ->map(fn (WarehouseLocationModel $m) => $this->toEntity($m));
    }

    public function getDescendants(int $locationId): Collection
    {
        $location = $this->model->newQueryWithoutScopes()->find($locationId);

        if ($location === null) {
            return collect();
        }

        $prefix = $location->path . $locationId . '/%';

        return $this->model->newQueryWithoutScopes()
            ->where('path', 'like', $prefix)
            ->orderBy('path')
            ->get()
            ->map(fn (WarehouseLocationModel $m) => $this->toEntity($m));
    }

    public function create(array $data): WarehouseLocation
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?WarehouseLocation
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

    private function toEntity(WarehouseLocationModel $model): WarehouseLocation
    {
        return new WarehouseLocation(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            warehouseId: (int) $model->warehouse_id,
            parentId: $model->parent_id !== null ? (int) $model->parent_id : null,
            name: (string) $model->name,
            code: (string) $model->code,
            type: (string) $model->type,
            path: (string) $model->path,
            level: (int) $model->level,
            barcode: $model->barcode !== null ? (string) $model->barcode : null,
            isActive: (bool) $model->is_active,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
