<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComponentModel;

final class EloquentProductComponentRepository implements ProductComponentRepositoryInterface
{
    public function __construct(
        private readonly ProductComponentModel $model,
    ) {}

    public function findByProduct(int $productId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('product_id', $productId)
            ->get()
            ->map(fn (ProductComponentModel $m) => $this->toEntity($m));
    }

    public function addComponent(array $data): ProductComponent
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function removeComponent(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function update(int $id, array $data): ?ProductComponent
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    private function toEntity(ProductComponentModel $model): ProductComponent
    {
        return new ProductComponent(
            id: (int) $model->id,
            productId: (int) $model->product_id,
            componentId: (int) $model->component_id,
            componentVariantId: $model->component_variant_id !== null ? (int) $model->component_variant_id : null,
            quantity: (float) $model->quantity,
            unitOfMeasure: (string) $model->unit_of_measure,
            notes: $model->notes !== null ? (string) $model->notes : null,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
