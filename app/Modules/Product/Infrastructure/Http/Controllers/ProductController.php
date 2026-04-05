<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Application\Contracts\ProductServiceInterface;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        if ($request->filled('category_id')) {
            return response()->json(
                $this->service->getByCategory((int) $request->query('category_id'), $tenantId)
            );
        }

        if ($request->filled('type')) {
            return response()->json(
                $this->service->getByType((string) $request->query('type'), $tenantId)
            );
        }

        return response()->json($this->service->getAll($tenantId));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sku'             => 'required|string|max:100',
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:physical,service,digital,combo,variable',
            'category_id'     => 'nullable|integer',
            'description'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
            'unit_of_measure' => 'nullable|string|max:50',
            'weight'          => 'nullable|numeric',
            'dimensions'      => 'nullable|array',
            'images'          => 'nullable|array',
            'metadata'        => 'nullable|array',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $product = $this->service->createProduct($data);

        return response()->json($product, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->getProduct($id, $tenantId));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'sku'             => 'sometimes|string|max:100',
            'name'            => 'sometimes|string|max:255',
            'type'            => 'sometimes|in:physical,service,digital,combo,variable',
            'category_id'     => 'nullable|integer',
            'description'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
            'unit_of_measure' => 'nullable|string|max:50',
            'weight'          => 'nullable|numeric',
            'dimensions'      => 'nullable|array',
            'images'          => 'nullable|array',
            'metadata'        => 'nullable|array',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $product = $this->service->updateProduct($id, $data);

        return response()->json($product);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->deleteProduct($id, $tenantId);

        return response()->json(null, 204);
    }
}
