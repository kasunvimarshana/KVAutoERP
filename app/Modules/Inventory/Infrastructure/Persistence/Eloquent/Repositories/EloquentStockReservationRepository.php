<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\StockReservation;
use Modules\Inventory\Domain\RepositoryInterfaces\StockReservationRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockReservationModel;

class EloquentStockReservationRepository implements StockReservationRepositoryInterface
{
    public function __construct(
        private readonly StockReservationModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?StockReservation
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByReference(string $referenceType, int $referenceId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function findActiveByProduct(int $productId, int $locationId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->where('status', 'active')
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function create(array $data): StockReservation
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toDomain($record);
    }

    public function update(int $id, array $data): StockReservation
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

    private function toDomain(StockReservationModel $m): StockReservation
    {
        return new StockReservation(
            id:            $m->id,
            tenantId:      $m->tenant_id,
            productId:     $m->product_id,
            variantId:     $m->variant_id,
            locationId:    $m->location_id,
            quantity:      (float) $m->quantity,
            referenceType: $m->reference_type,
            referenceId:   $m->reference_id,
            expiresAt:     $m->expires_at
                ? new \DateTimeImmutable($m->expires_at->toDateTimeString())
                : null,
            status:        $m->status,
            createdAt:     $m->created_at
                ? new \DateTimeImmutable($m->created_at->toDateTimeString())
                : null,
            updatedAt:     $m->updated_at
                ? new \DateTimeImmutable($m->updated_at->toDateTimeString())
                : null,
        );
    }
}
