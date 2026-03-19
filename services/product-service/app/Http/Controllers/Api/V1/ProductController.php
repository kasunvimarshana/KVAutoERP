<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductPriceResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Product resource controller (v1).
 *
 * Thin controller — delegates all business logic to ProductService.
 */
final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceInterface $productService,
    ) {}

    /**
     * List products with filtering, sorting, and pagination.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            (int) $request->query('per_page', config('product_service.pagination.default_per_page', 15)),
            (int) config('product_service.pagination.max_per_page', 100),
        );
        $page = max(1, (int) $request->query('page', 1));

        $filters = $request->only(['search', 'type', 'status', 'category_id', 'sort_by', 'sort_dir']);

        $paginator = $this->productService->list($filters, $page, $perPage);

        $pagination = new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );

        return ApiResponse::paginated(
            ProductResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Create a new product.
     *
     * @param  StoreProductRequest  $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->create($request->validated());

            return ApiResponse::created(new ProductResource($product), 'Product created successfully.');
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    /**
     * Show a single product.
     *
     * @param  string  $product
     * @return JsonResponse
     */
    public function show(string $product): JsonResponse
    {
        try {
            $model = $this->productService->findOrFail($product);

            return ApiResponse::success(new ProductResource($model));
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Update a product.
     *
     * @param  UpdateProductRequest  $request
     * @param  string                $product
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, string $product): JsonResponse
    {
        try {
            $model = $this->productService->update($product, $request->validated());

            return ApiResponse::success(new ProductResource($model), 'Product updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Soft-delete a product.
     *
     * @param  string  $product
     * @return JsonResponse
     */
    public function destroy(string $product): JsonResponse
    {
        try {
            $this->productService->delete($product);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * List prices for a product.
     *
     * @param  string  $product
     * @return JsonResponse
     */
    public function getPrices(string $product): JsonResponse
    {
        try {
            $prices = $this->productService->getPrices($product);

            return ApiResponse::success(ProductPriceResource::collection($prices));
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Add a price to a product.
     *
     * @param  StoreProductPriceRequest  $request
     * @param  string                    $product
     * @return JsonResponse
     */
    public function addPrice(StoreProductPriceRequest $request, string $product): JsonResponse
    {
        try {
            $price = $this->productService->addPrice($product, $request->validated());

            return ApiResponse::created(new ProductPriceResource($price), 'Price added successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * List variants for a product.
     *
     * @param  string  $product
     * @return JsonResponse
     */
    public function getVariants(string $product): JsonResponse
    {
        try {
            $variants = $this->productService->getVariants($product);

            return ApiResponse::success($variants);
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Add a variant to a product.
     *
     * @param  Request  $request
     * @param  string   $product
     * @return JsonResponse
     */
    public function addVariant(Request $request, string $product): JsonResponse
    {
        $data = $request->validate([
            'sku'        => 'required|string|max:100',
            'name'       => 'required|string|max:255',
            'attributes' => 'nullable|array',
            'is_active'  => 'nullable|boolean',
        ]);

        try {
            $variant = $this->productService->addVariant($product, $data);

            return ApiResponse::created($variant, 'Variant added successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }
}
