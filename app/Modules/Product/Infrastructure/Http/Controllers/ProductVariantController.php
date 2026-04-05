<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Application\Contracts\ProductVariantServiceInterface;

class ProductVariantController extends Controller
{
    public function __construct(
        private readonly ProductVariantServiceInterface $service,
    ) {}

    public function index(Request $request, int $productId): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->getByProduct($productId, $tenantId));
    }

    public function store(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'sku'        => 'required|string|max:100',
            'name'       => 'required|string|max:255',
            'attributes' => 'required|array',
            'price'      => 'nullable|numeric|min:0',
            'cost'       => 'nullable|numeric|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['tenant_id']  = (int) $request->header('X-Tenant-ID', 0);
        $data['product_id'] = $productId;

        $variant = $this->service->createVariant($data);

        return response()->json($variant, 201);
    }

    public function show(Request $request, int $productId, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->getVariant($id, $tenantId));
    }

    public function update(Request $request, int $productId, int $id): JsonResponse
    {
        $data = $request->validate([
            'sku'        => 'sometimes|string|max:100',
            'name'       => 'sometimes|string|max:255',
            'attributes' => 'sometimes|array',
            'price'      => 'nullable|numeric|min:0',
            'cost'       => 'nullable|numeric|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['tenant_id']  = (int) $request->header('X-Tenant-ID', 0);
        $data['product_id'] = $productId;

        $variant = $this->service->updateVariant($id, $data);

        return response()->json($variant);
    }

    public function destroy(Request $request, int $productId, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->deleteVariant($id, $tenantId);

        return response()->json(null, 204);
    }
}
