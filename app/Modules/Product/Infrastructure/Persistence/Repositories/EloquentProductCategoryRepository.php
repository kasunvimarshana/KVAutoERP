<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\ProductCategory;
use Modules\Product\Domain\Repositories\ProductCategoryRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Models\ProductCategoryClosureModel;
use Modules\Product\Infrastructure\Persistence\Models\ProductCategoryModel;

class EloquentProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    public function __construct(
        private readonly ProductCategoryModel $model,
        private readonly ProductCategoryClosureModel $closureModel,
    ) {}

    public function findById(int $id): ?ProductCategory
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (ProductCategoryModel $m) => $this->toEntity($m));
    }

    public function insertNode(array $data, ?int $parentId): ProductCategory
    {
        $model = $this->model->create(array_merge($data, ['parent_id' => $parentId]));

        $this->closureModel->create([
            'ancestor_id'   => $model->id,
            'descendant_id' => $model->id,
            'depth'         => 0,
        ]);

        if ($parentId !== null) {
            $ancestorRows = $this->closureModel->where('descendant_id', $parentId)->get();
            foreach ($ancestorRows as $row) {
                $this->closureModel->create([
                    'ancestor_id'   => $row->ancestor_id,
                    'descendant_id' => $model->id,
                    'depth'         => $row->depth + 1,
                ]);
            }
        }

        return $this->toEntity($model);
    }

    public function updateNode(int $id, array $data): ProductCategory
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function deleteNode(int $id): bool
    {
        $model = $this->model->find($id);
        if (! $model) {
            return false;
        }

        $this->closureModel->where('descendant_id', $id)->delete();

        return (bool) $model->delete();
    }

    public function getTree(int $tenantId): array
    {
        $all = $this->model
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ProductCategoryModel $m) => $this->toEntity($m))
            ->all();

        return $this->buildTree($all, null);
    }

    public function getDescendants(int $id): array
    {
        $ids = $this->closureModel
            ->where('ancestor_id', $id)
            ->where('descendant_id', '!=', $id)
            ->orderBy('depth')
            ->pluck('descendant_id')
            ->toArray();

        return $this->model
            ->whereIn('id', $ids)
            ->get()
            ->map(fn (ProductCategoryModel $m) => $this->toEntity($m))
            ->all();
    }

    private function buildTree(array $items, ?int $parentId): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item->parentId === $parentId) {
                $node = (array) $item;
                $node['children'] = $this->buildTree($items, $item->id);
                $tree[] = $node;
            }
        }

        return $tree;
    }

    private function toEntity(ProductCategoryModel $model): ProductCategory
    {
        return new ProductCategory(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            parentId: $model->parent_id,
            image: $model->image,
            isActive: (bool) $model->is_active,
            sortOrder: (int) $model->sort_order,
            metadata: $model->metadata,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
        );
    }
}
