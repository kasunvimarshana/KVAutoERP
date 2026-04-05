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

    public function findById(int $id): ?PriceListItem
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByPriceList(int $priceListId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('price_list_id', $priceListId)
            ->get()
            ->map(fn (PriceListItemModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByProduct(int $priceListId, int $productId, ?int $variantId): array
    {
        $query = $this->model->newQueryWithoutScopes()
            ->where('price_list_id', $priceListId)
            ->where('product_id', $productId);

        if ($variantId !== null) {
            $query->where(function ($q) use ($variantId) {
                $q->where('variant_id', $variantId)->orWhereNull('variant_id');
            });
        } else {
            $query->whereNull('variant_id');
        }

        return $query->get()
            ->map(fn (PriceListItemModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): PriceListItem
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?PriceListItem
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

    private function toEntity(PriceListItemModel $model): PriceListItem
    {
        return new PriceListItem(
            id: $model->id,
            priceListId: $model->price_list_id,
            productId: $model->product_id,
            variantId: $model->variant_id,
            priceType: $model->price_type,
            price: (float) $model->price,
            minQuantity: (float) $model->min_quantity,
            maxQuantity: $model->max_quantity !== null ? (float) $model->max_quantity : null,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
