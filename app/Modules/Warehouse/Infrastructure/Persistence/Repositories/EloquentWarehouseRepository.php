<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Models\WarehouseModel;

class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function __construct(
        private readonly WarehouseModel $model,
    ) {}

    public function findById(int $id): ?Warehouse
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
            ->through(fn (WarehouseModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Warehouse
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): Warehouse
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = $this->model->find($id);
        if (! $model) {
            return false;
        }

        return (bool) $model->delete();
    }

    private function toEntity(WarehouseModel $model): Warehouse
    {
        return new Warehouse(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            address: $model->address,
            isActive: (bool) $model->is_active,
            managerUserId: $model->manager_user_id,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
        );
    }
}
