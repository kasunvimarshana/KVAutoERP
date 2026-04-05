<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Pricing\Domain\Entities\PriceListItem;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListItemRepositoryInterface;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListItemModel;

class EloquentPriceListItemRepository implements PriceListItemRepositoryInterface
{
    public function __construct(
        private readonly PriceListItemModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?PriceListItem
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findByPriceList(int $priceListId, int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('price_list_id', $priceListId)
            ->get()
            ->map(fn (PriceListItemModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findByProduct(int $productId, int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->get()
            ->map(fn (PriceListItemModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): PriceListItem
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): PriceListItem
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->findOrFail($id);

        $record->update($data);

        return $this->toDomain($record->fresh());
    }

    public function delete(int $id, int $tenantId): bool
    {
        return (bool) $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    private function toDomain(PriceListItemModel $model): PriceListItem
    {
        return new PriceListItem(
            id:           $model->id,
            tenantId:     $model->tenant_id,
            priceListId:  $model->price_list_id,
            productId:    $model->product_id,
            variantId:    $model->variant_id,
            priceType:    $model->price_type,
            price:        (float) $model->price,
            minQuantity:  (float) $model->min_quantity,
            maxQuantity:  $model->max_quantity !== null ? (float) $model->max_quantity : null,
            notes:        $model->notes,
        );
    }
}
