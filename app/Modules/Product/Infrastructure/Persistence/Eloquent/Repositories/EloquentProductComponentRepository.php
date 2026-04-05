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

    public function findById(int $id, int $tenantId): ?ProductComponent
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findByProduct(int $productId, int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->get()
            ->map(fn (ProductComponentModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): ProductComponent
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): ProductComponent
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

    private function toDomain(ProductComponentModel $model): ProductComponent
    {
        return new ProductComponent(
            id:                 $model->id,
            tenantId:           $model->tenant_id,
            productId:          $model->product_id,
            componentProductId: $model->component_product_id,
            quantity:           (float) $model->quantity,
            unit:               $model->unit,
            notes:              $model->notes,
        );
    }
}
