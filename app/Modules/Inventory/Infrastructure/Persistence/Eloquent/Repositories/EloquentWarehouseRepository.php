<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Inventory\Domain\Entities\Warehouse;
use Modules\Inventory\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\WarehouseModel;

final class EloquentWarehouseRepository implements WarehouseRepositoryInterface
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

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (WarehouseModel $m) => $this->toEntity($m));
    }

    public function findDefault(int $tenantId): ?Warehouse
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): Warehouse
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Warehouse
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

    private function toEntity(WarehouseModel $model): Warehouse
    {
        return new Warehouse(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            name: (string) $model->name,
            code: (string) $model->code,
            address: $model->address,
            isActive: (bool) $model->is_active,
            isDefault: (bool) $model->is_default,
            managerId: $model->manager_id !== null ? (int) $model->manager_id : null,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
