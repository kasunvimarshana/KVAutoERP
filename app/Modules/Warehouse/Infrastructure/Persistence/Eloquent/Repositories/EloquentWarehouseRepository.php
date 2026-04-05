<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\WarehouseModel;

class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function __construct(
        private readonly WarehouseModel $model,
    ) {}

    public function findById(int $id): ?Warehouse
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, string $code): ?Warehouse
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findDefault(int $tenantId): ?Warehouse
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findActive(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->map(fn (WarehouseModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Warehouse
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Warehouse
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

    public function all(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (WarehouseModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(WarehouseModel $model): Warehouse
    {
        return new Warehouse(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            address: $model->address ?? [],
            isDefault: (bool) $model->is_default,
            isActive: (bool) $model->is_active,
            managerId: $model->manager_id,
            notes: $model->notes,
            createdAt: $model->created_at,
        );
    }
}
