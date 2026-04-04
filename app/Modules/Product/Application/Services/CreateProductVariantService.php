<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\CreateProductVariantServiceInterface;
use Modules\Product\Application\DTOs\CreateVariantData;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\Events\ProductVariantCreated;
use Modules\Product\Domain\Repositories\ProductVariantRepositoryInterface;

class CreateProductVariantService implements CreateProductVariantServiceInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $repository,
    ) {}

    public function execute(CreateVariantData $data): ProductVariant
    {
        return DB::transaction(function () use ($data): ProductVariant {
            $variant = $this->repository->create([
                'tenant_id'        => $data->tenantId,
                'product_id'       => $data->productId,
                'name'             => $data->name,
                'sku'              => $data->sku,
                'barcode'          => $data->barcode,
                'attributes'       => $data->attributes,
                'price'            => $data->price,
                'cost'             => $data->cost,
                'weight'           => $data->weight,
                'is_active'        => $data->isActive,
                'stock_management' => $data->stockManagement,
                'created_by'       => $data->createdBy,
                'updated_by'       => $data->createdBy,
            ]);

            Event::dispatch(new ProductVariantCreated($variant));

            return $variant;
        });
    }
}
