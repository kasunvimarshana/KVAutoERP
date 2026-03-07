<?php

namespace App\Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Requests\StoreProductRequest;
use App\Modules\Product\Requests\UpdateProductRequest;
use App\Modules\Product\Resources\ProductCollection;
use App\Modules\Product\Resources\ProductResource;
use App\Modules\Product\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductServiceInterface $productService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts($request->query());

            return (new ProductCollection($products))->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve products.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->productService->getProduct($id);

            $resource = (new ProductResource($result['product']))->withInventory($result['inventory']);

            return $resource->response()->setStatusCode(200);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 404;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve product.', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return (new ProductResource($product))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create product.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());

            return (new ProductResource($product))->response()->setStatusCode(200);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 404;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->deleteProduct($id);

            return response()->json(['message' => 'Product deleted successfully.'], 200);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 404;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product.', 'error' => $e->getMessage()], 500);
        }
    }
}
