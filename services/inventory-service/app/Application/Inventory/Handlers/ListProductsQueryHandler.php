<?php

declare(strict_types=1);

namespace App\Application\Inventory\Handlers;

use App\Application\Inventory\Queries\ListProductsQuery;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Handles ListProductsQuery.
 *
 * Builds filter/sort arrays from the query object and delegates
 * to the ProductRepository for efficient DB-level filtering.
 */
final class ListProductsQueryHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Execute the query.
     *
     * @return array|LengthAwarePaginator
     */
    public function handle(ListProductsQuery $query): array|LengthAwarePaginator
    {
        if ($query->lowStockOnly) {
            $products = $this->productRepository->findLowStock($query->tenantId);

            if ($query->perPage > 0) {
                return $this->productRepository->paginate(
                    source: $products,
                    perPage: $query->perPage,
                    page: $query->page,
                );
            }

            return $products;
        }

        if ($query->outOfStockOnly) {
            $products = $this->productRepository->findOutOfStock($query->tenantId);

            if ($query->perPage > 0) {
                return $this->productRepository->paginate(
                    source: $products,
                    perPage: $query->perPage,
                    page: $query->page,
                );
            }

            return $products;
        }

        // Build filters array.
        $filters = array_merge($query->filters, ['tenant_id' => $query->tenantId]);

        if ($query->sku !== null) {
            $filters['sku'] = $query->sku;
        }

        if ($query->status !== null) {
            $filters['status'] = $query->status;
        }

        if ($query->categoryId !== null) {
            $filters['category_id'] = $query->categoryId;
        }

        if ($query->minPrice !== null) {
            $filters['price'] = ['>=', $query->minPrice];
        }

        if ($query->maxPrice !== null) {
            $filters['price'] = ['<=', $query->maxPrice];
        }

        if ($query->search !== null) {
            $filters['search'] = $query->search;
        }

        if ($query->perPage > 0) {
            $filters['per_page'] = $query->perPage;
            $filters['page']     = $query->page;
        }

        return $this->productRepository->findAll(
            filters: $filters,
            sorts: $query->sorts,
            perPage: $query->perPage,
            page: $query->page,
        );
    }
}
