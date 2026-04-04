<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\UpdateProductCategoryServiceInterface;
use Modules\Product\Application\DTOs\UpdateCategoryData;
use Modules\Product\Domain\Entities\ProductCategory;
use Modules\Product\Domain\Exceptions\ProductCategoryNotFoundException;
use Modules\Product\Domain\Repositories\ProductCategoryRepositoryInterface;

class UpdateProductCategoryService implements UpdateProductCategoryServiceInterface
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateCategoryData $data): ProductCategory
    {
        return DB::transaction(function () use ($id, $data): ProductCategory {
            $existing = $this->repository->findById($id);
            if ($existing === null) {
                throw new ProductCategoryNotFoundException($id);
            }

            $updateData = array_filter([
                'name'        => $data->name,
                'slug'        => $data->slug,
                'description' => $data->description,
                'image'       => $data->image,
                'is_active'   => $data->isActive,
                'sort_order'  => $data->sortOrder,
                'metadata'    => $data->metadata,
                'updated_by'  => $data->updatedBy,
            ], fn ($v) => $v !== null);

            return $this->repository->updateNode($id, $updateData);
        });
    }
}
