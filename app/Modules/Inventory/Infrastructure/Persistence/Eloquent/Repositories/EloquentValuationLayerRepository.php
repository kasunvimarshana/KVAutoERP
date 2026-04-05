<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;

class EloquentValuationLayerRepository implements ValuationLayerRepositoryInterface
{
    public function __construct(
        private readonly ValuationLayerModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?ValuationLayer
    {
        $record = $this->query($tenantId)->where('id', $id)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function findByProductAndLocation(
        int $productId,
        ?int $variantId,
        int $locationId,
        int $tenantId,
        string $method,
    ): array {
        $query = $this->query($tenantId)
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->where('valuation_method', $method);

        if ($variantId === null) {
            $query->whereNull('variant_id');
        } else {
            $query->where('variant_id', $variantId);
        }

        return $query->get()->map(fn ($r) => $this->toDomain($r))->all();
    }

    public function create(array $data): ValuationLayer
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toDomain($record);
    }

    public function update(int $id, array $data): ValuationLayer
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->firstOrFail();
        $record->update($data);
        return $this->toDomain($record->fresh());
    }

    public function getLayersForConsumption(
        int $productId,
        ?int $variantId,
        int $locationId,
        int $tenantId,
        string $method,
    ): array {
        $query = $this->query($tenantId)
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->where('remaining_quantity', '>', 0);

        if ($variantId === null) {
            $query->whereNull('variant_id');
        } else {
            $query->where('variant_id', $variantId);
        }

        if ($method === 'fefo') {
            // Join with batch_lots to order by expiry_date ascending (nulls last)
            $query->leftJoin('batch_lots', 'batch_lots.id', '=', 'valuation_layers.batch_lot_id')
                  ->select('valuation_layers.*')
                  ->orderByRaw('COALESCE(batch_lots.expiry_date, \'9999-12-31\') ASC');
        } elseif ($method === 'lifo') {
            $query->orderBy('received_at', 'desc');
        } else {
            // fifo (default, also used for 'average')
            $query->orderBy('received_at', 'asc');
        }

        return $query->get()->map(fn ($r) => $this->toDomain($r))->all();
    }

    private function query(int $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);
    }

    private function toDomain(ValuationLayerModel $m): ValuationLayer
    {
        return new ValuationLayer(
            id:               $m->id,
            tenantId:         $m->tenant_id,
            productId:        $m->product_id,
            variantId:        $m->variant_id,
            locationId:       $m->location_id,
            batchLotId:       $m->batch_lot_id,
            quantity:         (float) $m->quantity,
            remainingQuantity: (float) $m->remaining_quantity,
            unitCost:         (float) $m->unit_cost,
            valuationMethod:  $m->valuation_method,
            receivedAt:       new \DateTimeImmutable($m->received_at->toDateTimeString()),
            reference:        $m->reference,
            createdAt:        $m->created_at
                ? new \DateTimeImmutable($m->created_at->toDateTimeString())
                : null,
        );
    }
}
