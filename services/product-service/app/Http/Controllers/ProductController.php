<?php
namespace App\Http\Controllers;
use App\Exceptions\ServiceException;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);
        $products = $this->productService->list($tenantId, $request->all());
        return $this->collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->create($this->tenantId($request), $request->validated());
            return response()->json(new ProductResource($product), 201);
        } catch (ServiceException $e) {
            return $this->error($e);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            return response()->json(new ProductResource($this->productService->get($id, $this->tenantId($request))));
        } catch (ServiceException $e) {
            return $this->error($e);
        }
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            return response()->json(new ProductResource($this->productService->update($id, $this->tenantId($request), $request->validated())));
        } catch (ServiceException $e) {
            return $this->error($e);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->productService->delete($id, $this->tenantId($request));
            return response()->json(['success' => true, 'message' => 'Product deleted.']);
        } catch (ServiceException $e) {
            return $this->error($e);
        }
    }

    /**
     * Cross-service lookup endpoint.
     * GET /api/products/lookup?ids[]=<uuid>&codes[]=<code>
     * Used by Inventory and Order services to resolve product attributes.
     */
    public function lookup(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);
        $ids      = (array) $request->input('ids', []);
        $codes    = (array) $request->input('codes', []);
        $products = $this->productService->lookup($tenantId, $ids, $codes);
        return response()->json(['success' => true, 'data' => ProductResource::collection($products)]);
    }

    private function tenantId(Request $request): string
    {
        return $request->attributes->get('tenant_id', $request->header('X-Tenant-ID', ''));
    }

    private function collection(mixed $data): JsonResponse
    {
        $isPaginated = $data instanceof \Illuminate\Pagination\AbstractPaginator;
        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($isPaginated ? $data->getCollection() : $data),
            'meta'    => $isPaginated ? ['total' => $data->total(), 'current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage()] : null,
        ]);
    }

    private function error(ServiceException $e): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
    }
}
