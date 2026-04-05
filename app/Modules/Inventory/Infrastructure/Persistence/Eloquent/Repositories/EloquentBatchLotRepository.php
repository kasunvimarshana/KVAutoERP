<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\BatchLot;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchLotRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\BatchLotModel;

class EloquentBatchLotRepository implements BatchLotRepositoryInterface
{
    public function __construct(
        private readonly BatchLotModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?BatchLot
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByNumber(string $batchNumber, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('batch_number', $batchNumber)
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function findActive(int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('status', 'active')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function findExpiring(int $days, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days)->toDateString())
            ->orderBy('expiry_date')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function findByProduct(int $productId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('product_id', $productId)
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function create(array $data): BatchLot
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toDomain($record);
    }

    public function update(int $id, array $data): BatchLot
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
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

    private function toDomain(BatchLotModel $m): BatchLot
    {
        return new BatchLot(
            id:                 $m->id,
            tenantId:           $m->tenant_id,
            productId:          $m->product_id,
            variantId:          $m->variant_id,
            batchNumber:        $m->batch_number,
            lotNumber:          $m->lot_number,
            serialNumber:       $m->serial_number,
            expiryDate:         $m->expiry_date
                ? new \DateTimeImmutable($m->expiry_date->toDateString())
                : null,
            manufacturingDate:  $m->manufacturing_date
                ? new \DateTimeImmutable($m->manufacturing_date->toDateString())
                : null,
            quantity:           (float) $m->quantity,
            remainingQuantity:  (float) $m->remaining_quantity,
            locationId:         $m->location_id,
            status:             $m->status,
            metadata:           $m->metadata,
            createdAt:          $m->created_at
                ? new \DateTimeImmutable($m->created_at->toDateTimeString())
                : null,
            updatedAt:          $m->updated_at
                ? new \DateTimeImmutable($m->updated_at->toDateTimeString())
                : null,
        );
    }
}
