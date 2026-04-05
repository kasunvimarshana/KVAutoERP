<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\Stock;
use Modules\Inventory\Domain\RepositoryInterfaces\StockRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockModel;

class EloquentStockRepository implements StockRepositoryInterface
{
    public function __construct(
        private readonly StockModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?Stock
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByProductAndLocation(
        int $productId,
        ?int $variantId,
        int $locationId,
        int $tenantId,
    ): ?Stock {
        $query = $this->query($tenantId)
            ->where('product_id', $productId)
            ->where('location_id', $locationId);

        if ($variantId === null) {
            $query->whereNull('variant_id');
        } else {
            $query->where('variant_id', $variantId);
        }

        $record = $query->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByProduct(int $productId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('product_id', $productId)
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function findByLocation(int $locationId, int $tenantId): array
    {
        return $this->query($tenantId)
            ->where('location_id', $locationId)
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->query($tenantId)
            ->get()
            ->map(fn ($r) => $this->toDomain($r))
            ->all();
    }

    public function upsert(array $data): Stock
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->updateOrCreate(
                [
                    'tenant_id'   => $data['tenant_id'],
                    'product_id'  => $data['product_id'],
                    'variant_id'  => $data['variant_id'] ?? null,
                    'location_id' => $data['location_id'],
                ],
                $data,
            );

        return $this->toDomain($record);
    }

    public function updateQuantity(int $id, float $quantityDelta, int $tenantId): Stock
    {
        $record = $this->query($tenantId)->where('id', $id)->firstOrFail();
        $record->quantity        = $record->quantity + $quantityDelta;
        $record->last_movement_at = now();
        $record->save();

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

    private function toDomain(StockModel $m): Stock
    {
        return new Stock(
            id:               $m->id,
            tenantId:         $m->tenant_id,
            productId:        $m->product_id,
            variantId:        $m->variant_id,
            locationId:       $m->location_id,
            quantity:         (float) $m->quantity,
            reservedQuantity: (float) $m->reserved_quantity,
            unit:             $m->unit,
            lastMovementAt:   $m->last_movement_at
                ? new \DateTimeImmutable($m->last_movement_at->toDateTimeString())
                : null,
        );
    }
}
