<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Core\Abstracts\Services\BaseService;
use App\Modules\Inventory\Infrastructure\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * InventoryService
 *
 * Orchestrates all inventory business logic: product lifecycle,
 * stock management, and low-stock monitoring.
 */
class InventoryService extends BaseService
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    // -------------------------------------------------------------------------
    //  Queries
    // -------------------------------------------------------------------------

    public function list(
        array $filters = [],
        array $sort = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection {
        return $this->productRepository->all(
            filters: $filters,
            sort:    $sort,
            perPage: $perPage,
            page:    $page
        );
    }

    public function search(
        string $term,
        array $filters = [],
        array $sort = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection {
        return $this->productRepository->search(
            term:    $term,
            filters: $filters,
            sort:    $sort,
            perPage: $perPage,
            page:    $page
        );
    }

    public function findById(int|string $id): Model
    {
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new \RuntimeException("Product [{$id}] not found.");
        }

        return $product;
    }

    public function findBySku(string $sku): ?Model
    {
        return $this->productRepository->findBySku($sku);
    }

    public function lowStockAlert(
        int $threshold = 10,
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection {
        return $this->productRepository->lowStock($threshold, $perPage, $page);
    }

    // -------------------------------------------------------------------------
    //  Mutations
    // -------------------------------------------------------------------------

    public function create(array $data): Model
    {
        return $this->productRepository->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        return $this->productRepository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->productRepository->delete($id);
    }

    // -------------------------------------------------------------------------
    //  Stock operations (called by Saga steps)
    // -------------------------------------------------------------------------

    /**
     * Reserve stock for an order line item (Saga Step 1).
     *
     * @throws \RuntimeException on insufficient stock
     */
    public function reserveStock(int|string $productId, int $quantity): void
    {
        $product = $this->findById($productId);
        $product->reserve($quantity);
    }

    /**
     * Release a prior stock reservation (Saga compensating action).
     */
    public function releaseReservation(int|string $productId, int $quantity): void
    {
        $product = $this->findById($productId);
        $product->releaseReservation($quantity);
    }

    /**
     * Deduct stock on successful order fulfilment (Saga Step 3).
     */
    public function deductOnFulfilment(int|string $productId, int $quantity): void
    {
        $product = $this->findById($productId);
        $product->deductOnFulfilment($quantity);
    }
}
