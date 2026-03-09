<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Inventory\Commands\CreateCategoryCommand;
use App\Domain\Inventory\Repositories\CategoryRepositoryInterface;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId   = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $categories = $this->categoryRepository->findByTenant($tenantId);
        return response()->json(['data' => array_map(fn ($c) => (new CategoryResource($c))->toArray($request), $categories)]);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $data = $this->categoryRepository->findById($id);
        if (!$data) {
            return response()->json(['error' => 'Category not found.'], 404);
        }
        return response()->json(['data' => (new CategoryResource($data))->toArray($request)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|uuid',
            'description' => 'nullable|string|max:2000',
            'is_active'   => 'nullable|boolean',
        ]);
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $data = $this->categoryRepository->create([
            'id'          => Str::uuid()->toString(),
            'tenant_id'   => $tenantId,
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'parent_id'   => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? '',
            'is_active'   => $validated['is_active'] ?? true,
        ]);
        return response()->json(['data' => (new CategoryResource($data))->toArray($request)], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:2000',
            'is_active'   => 'sometimes|boolean',
        ]);
        $data = $this->categoryRepository->update($id, $validated);
        return response()->json(['data' => (new CategoryResource($data))->toArray($request)]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->categoryRepository->delete($id);
        return response()->json(['message' => 'Category deleted.']);
    }
}
