<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Domain\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $categories = Category::where('tenant_id', $tenantId)
            ->with('children')
            ->whereNull('parent_id')
            ->paginate($request->input('per_page', 15));

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $category = Category::create(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json(['success' => true, 'data' => $category], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::with(['parent', 'children', 'products'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $category]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'parent_id' => 'sometimes|nullable|integer|exists:categories,id',
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        $category = Category::findOrFail($id);
        $category->update($validated);
        return response()->json(['success' => true, 'data' => $category]);
    }

    public function destroy(int $id): JsonResponse
    {
        Category::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }
}
