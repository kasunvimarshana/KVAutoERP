<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\CategoryServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Product category controller (v1).
 */
final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
    ) {}

    /**
     * List categories with optional search and pagination.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            (int) $request->query('per_page', 15),
            100,
        );
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['search', 'is_active', 'parent_id']);

        $paginator = $this->categoryService->list($filters, $page, $perPage);

        $pagination = new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );

        return ApiResponse::paginated(
            CategoryResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Create a new category.
     *
     * @param  StoreCategoryRequest  $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return ApiResponse::created(new CategoryResource($category), 'Category created successfully.');
    }

    /**
     * Show a single category.
     *
     * @param  string  $category
     * @return JsonResponse
     */
    public function show(string $category): JsonResponse
    {
        try {
            $model = $this->categoryService->findOrFail($category);

            return ApiResponse::success(new CategoryResource($model));
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Update a category.
     *
     * @param  StoreCategoryRequest  $request
     * @param  string                $category
     * @return JsonResponse
     */
    public function update(StoreCategoryRequest $request, string $category): JsonResponse
    {
        try {
            $model = $this->categoryService->update($category, $request->validated());

            return ApiResponse::success(new CategoryResource($model), 'Category updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Delete a category.
     *
     * @param  string  $category
     * @return JsonResponse
     */
    public function destroy(string $category): JsonResponse
    {
        try {
            $this->categoryService->delete($category);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }
}
