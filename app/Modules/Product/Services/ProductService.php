<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Saga\SagaOrchestrator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly SagaOrchestrator $sagaOrchestrator,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    public function findById(int $id): ?Product
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->repository->create($data);

            event(new ProductCreated($product));

            Log::info('Product created', ['product_id' => $product->id, 'sku' => $product->sku]);

            return $product;
        });
    }

    public function update(int $id, array $data): ?Product
    {
        $product = $this->repository->find($id);
        if (!$product) {
            return null;
        }

        $originalData = $product->toArray();

        return $this->sagaOrchestrator->execute(
            steps: [
                [
                    'action'     => fn() => DB::transaction(fn() => $this->repository->update($id, $data)),
                    'compensate' => fn() => DB::transaction(fn() => $this->repository->update($id, array_intersect_key($originalData, array_flip(['name', 'description', 'price', 'stock', 'sku', 'is_active'])))),
                ],
                [
                    'action'     => function () use ($id, $data) {
                        $updated = $this->repository->find($id);
                        event(new ProductUpdated($updated));
                        return $updated;
                    },
                    'compensate' => fn() => Log::warning('ProductUpdated event compensation triggered', ['id' => $id]),
                ],
            ],
            finalResult: fn() => $this->repository->find($id),
        );
    }

    public function delete(int $id): bool
    {
        $product = $this->repository->find($id);
        if (!$product) {
            return false;
        }

        $productData = $product->toArray();

        return $this->sagaOrchestrator->execute(
            steps: [
                [
                    'action'     => fn() => DB::transaction(fn() => $this->repository->delete($id)),
                    'compensate' => fn() => DB::transaction(fn() => $this->repository->create(array_intersect_key($productData, array_flip(['name', 'description', 'price', 'stock', 'sku', 'is_active'])))),
                ],
                [
                    'action'     => function () use ($product) {
                        event(new ProductDeleted($product));
                        return true;
                    },
                    'compensate' => fn() => Log::warning('ProductDeleted event compensation triggered', ['product_id' => $product->id]),
                ],
            ],
            finalResult: fn() => true,
        );
    }
}
