<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\Batch;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\BatchModel;

class EloquentBatchRepository implements BatchRepositoryInterface
{
    public function __construct(
        private readonly BatchModel $model,
    ) {}

    public function findById(int $id): ?Batch
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByNumber(int $tenantId, string $batchNumber): ?Batch
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('batch_number', $batchNumber)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByProduct(int $tenantId, int $productId, ?int $variantId, ?string $status): array
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get()
            ->map(fn (BatchModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findExpiring(int $tenantId, int $days): array
    {
        $cutoff = now()->addDays($days);

        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $cutoff)
            ->orderBy('expiry_date')
            ->get()
            ->map(fn (BatchModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Batch
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Batch
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

    private function toEntity(BatchModel $model): Batch
    {
        return new Batch(
            id: $model->id,
            tenantId: $model->tenant_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            batchNumber: $model->batch_number,
            lotNumber: $model->lot_number,
            serialNumber: $model->serial_number,
            expiryDate: $model->expiry_date,
            manufactureDate: $model->manufacture_date,
            supplierId: $model->supplier_id,
            quantity: (float) $model->quantity,
            receivedQuantity: (float) $model->received_quantity,
            status: $model->status,
            warehouseId: $model->warehouse_id,
            locationId: $model->location_id,
            costPerUnit: (float) $model->cost_per_unit,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
