<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Application\Contracts\CreateProductCategoryServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductCategoryServiceInterface;
use Modules\Product\Application\Contracts\GetProductCategoryTreeServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductCategoryServiceInterface;
use Modules\Product\Application\DTOs\CreateCategoryData;
use Modules\Product\Application\DTOs\UpdateCategoryData;
use Modules\Product\Domain\Exceptions\ProductCategoryNotFoundException;
use Modules\Product\Domain\Repositories\ProductCategoryRepositoryInterface;
use Modules\Product\Infrastructure\Http\Requests\CreateProductCategoryRequest;
use Modules\Product\Infrastructure\Http\Requests\UpdateProductCategoryRequest;
use Modules\Product\Infrastructure\Http\Resources\ProductCategoryResource;

class ProductCategoryController extends Controller
{
    public function __construct(
        private readonly CreateProductCategoryServiceInterface $createService,
        private readonly UpdateProductCategoryServiceInterface $updateService,
        private readonly DeleteProductCategoryServiceInterface $deleteService,
        private readonly GetProductCategoryTreeServiceInterface $treeService,
        private readonly ProductCategoryRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $categories = $this->repository->findByTenant(
            $tenantId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => ProductCategoryResource::collection($categories->items()),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ],
        ]);
    }

    public function store(CreateProductCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateCategoryData::fromArray([
            'tenantId'    => $validated['tenant_id'],
            'name'        => $validated['name'],
            'slug'        => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'parentId'    => $validated['parent_id'] ?? null,
            'image'       => $validated['image'] ?? null,
            'isActive'    => $validated['is_active'] ?? true,
            'sortOrder'   => $validated['sort_order'] ?? 0,
            'metadata'    => $validated['metadata'] ?? null,
            'createdBy'   => $request->user()?->id,
        ]);

        $category = $this->createService->execute($data);

        return response()->json(new ProductCategoryResource($category), 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->repository->findById($id);
        if ($category === null) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json(new ProductCategoryResource($category));
    }

    public function update(UpdateProductCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $data = UpdateCategoryData::fromArray([
                'name'        => $validated['name'] ?? null,
                'slug'        => $validated['slug'] ?? null,
                'description' => $validated['description'] ?? null,
                'image'       => $validated['image'] ?? null,
                'isActive'    => $validated['is_active'] ?? null,
                'sortOrder'   => $validated['sort_order'] ?? null,
                'metadata'    => $validated['metadata'] ?? null,
                'updatedBy'   => $request->user()?->id,
            ]);

            $category = $this->updateService->execute($id, $data);

            return response()->json(new ProductCategoryResource($category));
        } catch (ProductCategoryNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteService->execute($id);

            return response()->json(null, 204);
        } catch (ProductCategoryNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function tree(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $tree = $this->treeService->execute($tenantId);

        return response()->json(['data' => $tree]);
    }
}
