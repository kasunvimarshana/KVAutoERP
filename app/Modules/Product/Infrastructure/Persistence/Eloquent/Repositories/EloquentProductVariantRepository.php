<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;

class EloquentProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function __construct(
        private readonly ProductVariantModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?ProductVariant
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findBySku(string $sku, int $tenantId): ?ProductVariant
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('sku', $sku)
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
            ->map(fn (ProductVariantModel $m) => $this->toDomain($m))
            ->all();
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (ProductVariantModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): ProductVariant
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): ProductVariant
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

    private function toDomain(ProductVariantModel $model): ProductVariant
    {
        return new ProductVariant(
            id:        $model->id,
            tenantId:  $model->tenant_id,
            productId: $model->product_id,
            sku:       $model->sku,
            name:      $model->name,
            attributes: $model->attributes ?? [],
            price:     $model->price !== null ? (float) $model->price : null,
            cost:      $model->cost !== null ? (float) $model->cost : null,
            isActive:  (bool) $model->is_active,
            createdAt: $model->created_at
                ? \DateTimeImmutable::createFromInterface($model->created_at)
                : null,
            updatedAt: $model->updated_at
                ? \DateTimeImmutable::createFromInterface($model->updated_at)
                : null,
        );
    }
}
