<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Events\ProductCreated;
use App\Events\ProductDeleted;
use App\Events\ProductUpdated;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(private readonly ProductRepository $productRepository) {}

    public function listProducts(string $tenantId, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $repo = $this->productRepository->withTenant($tenantId);

        $query = $repo->newQuery();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'LIKE', '%' . $term . '%')
                  ->orWhere('sku', 'LIKE', '%' . $term . '%')
                  ->orWhere('category', 'LIKE', '%' . $term . '%');
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['low_stock'])) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
        }

        $sortColumn    = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_dir'] ?? 'desc';
        $allowedSorts  = ['name', 'sku', 'price', 'stock_quantity', 'created_at'];

        if (in_array($sortColumn, $allowedSorts, true)) {
            $query->orderBy($sortColumn, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProduct(string $tenantId, string $productId): ?ProductDTO
    {
        $product = $this->productRepository->withTenant($tenantId)->find($productId);

        return $product ? ProductDTO::fromModel($product) : null;
    }

    public function getProductBySku(string $tenantId, string $sku): ?ProductDTO
    {
        $product = $this->productRepository->withTenant($tenantId)->findBySku($sku);

        return $product ? ProductDTO::fromModel($product) : null;
    }

    public function createProduct(string $tenantId, array $data): ProductDTO
    {
        $this->ensureSkuUnique($tenantId, $data['sku']);

        $product = DB::transaction(function () use ($tenantId, $data): Product {
            $product = $this->productRepository->create([
                'tenant_id'       => $tenantId,
                'name'            => $data['name'],
                'sku'             => $data['sku'],
                'description'     => $data['description'] ?? null,
                'category'        => $data['category'] ?? null,
                'price'           => $data['price'],
                'cost'            => $data['cost'],
                'stock_quantity'  => $data['stock_quantity'],
                'min_stock_level' => $data['min_stock_level'],
                'unit'            => $data['unit'] ?? null,
                'status'          => $data['status'] ?? 'active',
                'metadata'        => $data['metadata'] ?? null,
            ]);

            event(new ProductCreated($product));

            return $product;
        });

        return ProductDTO::fromModel($product);
    }

    public function updateProduct(string $tenantId, string $productId, array $data): ?ProductDTO
    {
        $product = $this->productRepository->withTenant($tenantId)->find($productId);

        if ($product === null) {
            return null;
        }

        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            $this->ensureSkuUnique($tenantId, $data['sku'], $productId);
        }

        $updated = DB::transaction(function () use ($product, $data): Product {
            $product->fill($data)->save();
            event(new ProductUpdated($product->fresh()));
            return $product->fresh();
        });

        return ProductDTO::fromModel($updated);
    }

    public function deleteProduct(string $tenantId, string $productId): bool
    {
        $product = $this->productRepository->withTenant($tenantId)->find($productId);

        if ($product === null) {
            return false;
        }

        return DB::transaction(function () use ($product, $tenantId, $productId): bool {
            $sku     = $product->sku;
            $deleted = $this->productRepository->delete($productId);

            if ($deleted) {
                event(new ProductDeleted($productId, $tenantId, $sku));
            }

            return $deleted;
        });
    }

    private function ensureSkuUnique(string $tenantId, string $sku, ?string $excludeId = null): void
    {
        $query = Product::where('tenant_id', $tenantId)->where('sku', $sku);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'sku' => ['The SKU is already in use within this tenant.'],
            ]);
        }
    }
}
