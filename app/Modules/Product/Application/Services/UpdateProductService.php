<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\UpdateProductServiceInterface;
use Modules\Product\Application\DTOs\UpdateProductData;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Events\ProductUpdated;
use Modules\Product\Domain\Exceptions\ProductNotFoundException;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class UpdateProductService implements UpdateProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateProductData $data): Product
    {
        return DB::transaction(function () use ($id, $data): Product {
            $existing = $this->repository->findById($id);
            if ($existing === null) {
                throw new ProductNotFoundException($id);
            }

            $updateData = array_filter([
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
                'updated_by'        => $data->updatedBy,
            ], fn ($v) => $v !== null);

            $product = $this->repository->update($id, $updateData);

            Event::dispatch(new ProductUpdated($product));

            return $product;
        });
    }
}
