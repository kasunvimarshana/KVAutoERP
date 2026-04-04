<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\CreateProductCategoryServiceInterface;
use Modules\Product\Application\DTOs\CreateCategoryData;
use Modules\Product\Domain\Entities\ProductCategory;
use Modules\Product\Domain\Events\ProductCategoryCreated;
use Modules\Product\Domain\Repositories\ProductCategoryRepositoryInterface;

class CreateProductCategoryService implements CreateProductCategoryServiceInterface
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $repository,
    ) {}

    public function execute(CreateCategoryData $data): ProductCategory
    {
        return DB::transaction(function () use ($data): ProductCategory {
            $category = $this->repository->insertNode([
                'tenant_id'   => $data->tenantId,
                'name'        => $data->name,
                'slug'        => $data->slug,
                'description' => $data->description,
                'image'       => $data->image,
                'is_active'   => $data->isActive,
                'sort_order'  => $data->sortOrder,
                'metadata'    => $data->metadata,
                'created_by'  => $data->createdBy,
                'updated_by'  => $data->createdBy,
            ], $data->parentId);

            Event::dispatch(new ProductCategoryCreated($category));

            return $category;
        });
    }
}
