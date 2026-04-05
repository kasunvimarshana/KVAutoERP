<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComponentModel;

class EloquentProductComponentRepository implements ProductComponentRepositoryInterface
{
    public function __construct(
        private readonly ProductComponentModel $model,
    ) {}

    public function findByProduct(int $productId): array
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->get()
            ->map(fn (ProductComponentModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): ProductComponent
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?ProductComponent
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function toEntity(ProductComponentModel $model): ProductComponent
    {
        return new ProductComponent(
            id: $model->id,
            productId: $model->product_id,
            componentProductId: $model->component_product_id,
            componentVariantId: $model->component_variant_id,
            quantity: (float) $model->quantity,
            unit: $model->unit,
            notes: $model->notes,
        );
    }
}
