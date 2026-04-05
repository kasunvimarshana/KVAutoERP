<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?Product
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findBySku(string $sku, int $tenantId): ?Product
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('sku', $sku)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (ProductModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findByCategory(int $categoryId, int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('category_id', $categoryId)
            ->get()
            ->map(fn (ProductModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findByType(string $type, int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->get()
            ->map(fn (ProductModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): Product
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): Product
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

    private function toDomain(ProductModel $model): Product
    {
        return new Product(
            id:            $model->id,
            tenantId:      $model->tenant_id,
            sku:           $model->sku,
            name:          $model->name,
            type:          $model->type,
            categoryId:    $model->category_id,
            description:   $model->description,
            isActive:      (bool) $model->is_active,
            unitOfMeasure: $model->unit_of_measure,
            weight:        $model->weight !== null ? (float) $model->weight : null,
            dimensions:    $model->dimensions,
            images:        $model->images,
            metadata:      $model->metadata,
            createdAt:     $model->created_at
                ? \DateTimeImmutable::createFromInterface($model->created_at)
                : null,
            updatedAt:     $model->updated_at
                ? \DateTimeImmutable::createFromInterface($model->updated_at)
                : null,
        );
    }
}
