<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\StockLocation;
use Modules\Inventory\Domain\RepositoryInterfaces\StockLocationRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockLocationModel;

class EloquentStockLocationRepository implements StockLocationRepositoryInterface
{
    public function __construct(
        private readonly StockLocationModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?StockLocation
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByCode(string $code, int $tenantId): ?StockLocation
    {
        $record = $this->query($tenantId)->where('code', $code)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->query($tenantId)->get()->map(fn ($r) => $this->toDomain($r))->all();
    }

    public function findByWarehouse(int $warehouseId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function getTree(int $tenantId): array
    {
        $all    = $this->allByTenant($tenantId);
        $indexed = [];
        foreach ($all as $location) {
            $indexed[$location->id] = ['entity' => $location, 'children' => []];
        }

        $roots = [];
        foreach ($indexed as $id => $node) {
            $parentId = $node['entity']->parentId;
            if ($parentId === null || !isset($indexed[$parentId])) {
                $roots[] = &$indexed[$id];
            } else {
                $indexed[$parentId]['children'][] = &$indexed[$id];
            }
        }

        return $roots;
    }

    public function create(array $data): StockLocation
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toDomain($record);
    }

    public function update(int $id, array $data): StockLocation
    {
        $record = $this->query($data['tenant_id'] ?? 0)
            ->where('id', $id)
            ->firstOrFail();
        $record->update($data);
        return $this->toDomain($record->fresh());
    }

    public function delete(int $id, int $tenantId): bool
    {
        return (bool) $this->query($tenantId)->where('id', $id)->delete();
    }

    private function query(int $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);
    }

    private function toDomain(StockLocationModel $m): StockLocation
    {
        return new StockLocation(
            id:          $m->id,
            tenantId:    $m->tenant_id,
            warehouseId: $m->warehouse_id,
            code:        $m->code,
            name:        $m->name,
            type:        $m->type,
            parentId:    $m->parent_id,
            path:        $m->path ?? '',
            level:       (int) $m->level,
            isActive:    (bool) $m->is_active,
            metadata:    $m->metadata,
            createdAt:   $m->created_at ? new \DateTimeImmutable($m->created_at->toDateTimeString()) : null,
            updatedAt:   $m->updated_at ? new \DateTimeImmutable($m->updated_at->toDateTimeString()) : null,
        );
    }
}
