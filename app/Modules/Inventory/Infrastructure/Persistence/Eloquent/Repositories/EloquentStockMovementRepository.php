<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function __construct(
        private readonly StockMovementModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?StockMovement
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByProduct(int $productId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('product_id', $productId)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function findByLocation(int $locationId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where(function ($q) use ($locationId): void {
                $q->where('from_location_id', $locationId)
                  ->orWhere('to_location_id', $locationId);
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function findByBatch(string $batchNumber, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('batch_number', $batchNumber)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->query($tenantId)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function create(array $data): StockMovement
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toDomain($record);
    }

    private function query(int $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);
    }

    private function toDomain(StockMovementModel $m): StockMovement
    {
        return new StockMovement(
            id:             $m->id,
            tenantId:       $m->tenant_id,
            productId:      $m->product_id,
            variantId:      $m->variant_id,
            fromLocationId: $m->from_location_id,
            toLocationId:   $m->to_location_id,
            quantity:       (float) $m->quantity,
            type:           $m->type,
            reference:      $m->reference,
            batchNumber:    $m->batch_number,
            lotNumber:      $m->lot_number,
            serialNumber:   $m->serial_number,
            expiryDate:     $m->expiry_date
                ? new \DateTimeImmutable($m->expiry_date->toDateString())
                : null,
            cost:           $m->cost !== null ? (float) $m->cost : null,
            notes:          $m->notes,
            createdBy:      $m->created_by,
            createdAt:      $m->created_at
                ? new \DateTimeImmutable($m->created_at->toDateTimeString())
                : null,
        );
    }
}
