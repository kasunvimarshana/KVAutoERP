<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\DTOs\CreateProductData;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Events\ProductCreated;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class CreateProductService implements CreateProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(CreateProductData $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $product = $this->repository->create([
                'tenant_id'         => $data->tenantId,
                'name'              => $data->name,
                'sku'               => $data->sku,
                'barcode'           => $data->barcode,
                'type'              => $data->type,
                'status'            => $data->status,
                'category_id'       => $data->categoryId,
                'description'       => $data->description,
                'short_description' => $data->shortDescription,
                'weight'            => $data->weight,
                'dimensions'        => $data->dimensions,
                'images'            => $data->images,
                'tags'              => $data->tags,
                'is_taxable'        => $data->isTaxable,
                'tax_class'         => $data->taxClass,
                'has_serial'        => $data->hasSerial,
                'has_batch'         => $data->hasBatch,
                'has_lot'           => $data->hasLot,
                'is_serialized'     => $data->isSerialized,
                'created_by'        => $data->createdBy,
                'updated_by'        => $data->createdBy,
            ]);

            Event::dispatch(new ProductCreated($product));

            return $product;
        });
    }
}
