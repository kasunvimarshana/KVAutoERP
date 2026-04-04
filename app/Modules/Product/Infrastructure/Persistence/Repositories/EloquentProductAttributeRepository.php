<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\ProductAttribute;
use Modules\Product\Domain\Repositories\ProductAttributeRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Models\ProductAttributeModel;

class EloquentProductAttributeRepository implements ProductAttributeRepositoryInterface
{
    public function __construct(
        private readonly ProductAttributeModel $model,
    ) {}

    public function findById(int $id): ?ProductAttribute
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (ProductAttributeModel $m) => $this->toEntity($m));
    }

    public function create(array $data): ProductAttribute
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): ProductAttribute
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = $this->model->find($id);
        if (! $model) {
            return false;
        }

        return (bool) $model->delete();
    }

    private function toEntity(ProductAttributeModel $model): ProductAttribute
    {
        return new ProductAttribute(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            slug: $model->slug,
            type: $model->type,
            options: $model->options,
        );
    }
}
