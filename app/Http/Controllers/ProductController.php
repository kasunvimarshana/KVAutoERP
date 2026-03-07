<?php

namespace App\Http\Controllers;

use App\Events\ProductCreated;
use App\Events\ProductDeleted;
use App\Events\ProductUpdated;
use App\Http\Resources\ProductResource;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service A: Product Service Controller
 *
 * Handles full CRUD for products. Each mutating operation is wrapped in a
 * database transaction. On success, a domain event is dispatched so that
 * Service B (Inventory Service) can react accordingly. If the listener
 * (Service B) is synchronous and throws, the transaction is rolled back,
 * maintaining data consistency across services.
 */
class ProductController extends Controller
{
    /**
     * List all products together with their related inventory records.
     * GET /api/products
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::with('inventories')->get();

        return ProductResource::collection($products);
    }

    /**
     * Create a new product and initialise its inventory via event.
     * POST /api/products
     *
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'sku'         => 'required|string|max:100|unique:products,sku',
        ]);

        try {
            $product = DB::transaction(function () use ($validated) {
                $product = Product::create($validated);

                // Dispatch event so Service B creates the inventory record.
                // When the queue driver is 'sync', this runs inside the transaction,
                // and any exception from the listener will roll back the product creation.
                event(new ProductCreated($product));

                return $product;
            });

            $product->load('inventories');

            Log::info('Service A: Product created', ['product_id' => $product->id]);

            return response()->json([
                'message' => 'Product created successfully.',
                'data'    => new ProductResource($product),
            ], 201);
        } catch (Throwable $e) {
            Log::error('Service A: Failed to create product — transaction rolled back', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create product.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single product with its inventory.
     * GET /api/products/{product}
     */
    public function show(Product $product): ProductResource
    {
        $product->load('inventories');

        return new ProductResource($product);
    }

    /**
     * Update an existing product and propagate changes to inventory via event.
     * PUT /api/products/{product}
     *
     * @throws Throwable
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|required|numeric|min:0',
            'sku'         => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
        ]);

        try {
            DB::transaction(function () use ($validated, $product) {
                $product->update($validated);

                // Dispatch event so Service B keeps its product_name in sync.
                event(new ProductUpdated($product));
            });

            $product->load('inventories');

            Log::info('Service A: Product updated', ['product_id' => $product->id]);

            return response()->json([
                'message' => 'Product updated successfully.',
                'data'    => new ProductResource($product),
            ]);
        } catch (Throwable $e) {
            Log::error('Service A: Failed to update product — transaction rolled back', [
                'product_id' => $product->id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update product.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a product and remove its inventory records via event.
     * DELETE /api/products/{product}
     *
     * @throws Throwable
     */
    public function destroy(Product $product): JsonResponse
    {
        $productId   = $product->id;
        $productName = $product->name;

        try {
            DB::transaction(function () use ($product, $productId, $productName) {
                $product->delete();

                // Dispatch event so Service B removes the related inventory records.
                event(new ProductDeleted($productId, $productName));
            });

            Log::info('Service A: Product deleted', ['product_id' => $productId]);

            return response()->json([
                'message' => 'Product deleted successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Service A: Failed to delete product — transaction rolled back', [
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to delete product.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
