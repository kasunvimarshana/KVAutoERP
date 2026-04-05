<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Application\Contracts\ProductComponentServiceInterface;

class ProductComponentController extends Controller
{
    public function __construct(
        private readonly ProductComponentServiceInterface $service,
    ) {}

    public function index(Request $request, int $productId): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->getComponents($productId, $tenantId));
    }

    public function store(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'component_product_id' => 'required|integer',
            'quantity'             => 'required|numeric|min:0.0001',
            'unit'                 => 'nullable|string|max:50',
            'notes'                => 'nullable|string',
        ]);

        $data['tenant_id']  = (int) $request->header('X-Tenant-ID', 0);
        $data['product_id'] = $productId;

        $component = $this->service->addComponent($data);

        return response()->json($component, 201);
    }

    public function destroy(Request $request, int $productId, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->removeComponent($id, $tenantId);

        return response()->json(null, 204);
    }
}
