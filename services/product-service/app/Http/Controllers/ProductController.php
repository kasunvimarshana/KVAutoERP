<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Repositories\ProductRepository;
use App\Services\Pricing\ProductPricingService;
use Shared\Core\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductPricingService
     */
    protected $pricingService;

    public function __construct(ProductRepository $productRepository, ProductPricingService $pricingService)
    {
        $this->productRepository = $productRepository;
        $this->pricingService = $pricingService;
    }

    /**
     * Display a listing of products with dynamic pricing.
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->productRepository
            ->with(['category', 'baseUom'])
            ->paginate($request->get('per_page', 15));

        $data = ProductResource::collection($products)->response()->getData(true);

        // Calculate dynamic prices for the results
        foreach ($data['data'] as &$product) {
            $productModel = $products->find($product['id']);
            $product['final_price'] = $this->pricingService->getFinalPrice($productModel, [
                'location_id' => $request->get('location_id', 1),
                'currency_code' => $request->get('currency_code', 'USD'),
                'quantity' => 1
            ]);
        }

        return $this->success($data);
    }

    /**
     * Display the specified product with pricing context.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $product = $this->productRepository->with(['category', 'baseUom', 'prices', 'images'])->find($id);

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        $resource = new ProductResource($product);
        $data = $resource->toArray($request);
        
        $data['final_price'] = $this->pricingService->getFinalPrice($product, [
            'location_id' => $request->get('location_id', 1),
            'currency_code' => $request->get('currency_code', 'USD'),
            'quantity' => $request->get('quantity', 1)
        ]);

        return $this->success($data);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request): JsonResponse
    {
        $product = $this->productRepository->create($request->all());
        return $this->success(new ProductResource($product), 'Product created successfully', 201);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $updated = $this->productRepository->update($request->all(), $id);

        if (!$updated) {
            return $this->error('Product not found or update failed', 404);
        }

        return $this->success(null, 'Product updated successfully');
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id): JsonResponse
    {
        $deleted = $this->productRepository->delete($id);

        if (!$deleted) {
            return $this->error('Product not found or delete failed', 404);
        }

        return $this->success(null, 'Product deleted successfully');
    }
}
