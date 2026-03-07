<?php

namespace App\Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->productService->index($request->query()));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'sku'         => 'required|string|max:100|unique:products,sku',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'category'    => 'nullable|string|max:100',
            'attributes'  => 'nullable|array',
            'is_active'   => 'boolean',
        ]);

        return response()->json($this->productService->store($request->all()), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->productService->show($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'sku'         => 'sometimes|string|max:100|unique:products,sku,'.$id,
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'category'    => 'nullable|string|max:100',
            'attributes'  => 'nullable|array',
            'is_active'   => 'sometimes|boolean',
        ]);

        return response()->json($this->productService->update($id, $request->all()));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->productService->destroy($id);

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
