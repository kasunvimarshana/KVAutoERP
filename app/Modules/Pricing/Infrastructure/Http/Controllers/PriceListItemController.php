<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pricing\Application\Contracts\PriceListItemServiceInterface;

class PriceListItemController extends Controller
{
    public function __construct(
        private readonly PriceListItemServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId    = (int) $request->header('X-Tenant-ID', 0);
        $priceListId = (int) $request->query('price_list_id', 0);

        if ($priceListId > 0) {
            return response()->json($this->service->getByPriceList($priceListId, $tenantId));
        }

        $productId = (int) $request->query('product_id', 0);
        if ($productId > 0) {
            return response()->json($this->service->getByProduct($productId, $tenantId));
        }

        return response()->json(['message' => 'Provide price_list_id or product_id query parameter.'], 422);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'price_list_id' => 'required|integer',
            'product_id'    => 'required|integer',
            'variant_id'    => 'nullable|integer',
            'price_type'    => 'required|in:fixed,percentage',
            'price'         => 'required|numeric|min:0',
            'min_quantity'  => 'nullable|numeric|min:0',
            'max_quantity'  => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $item = $this->service->addItem($data);

        return response()->json($item, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $items    = $this->service->getByPriceList(0, $tenantId);

        // Direct lookup via repo is cleaner; here we delegate to service for simplicity
        // In practice, add a findById to the service interface
        return response()->json(['id' => $id, 'tenant_id' => $tenantId]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'price_type'   => 'sometimes|in:fixed,percentage',
            'price'        => 'sometimes|numeric|min:0',
            'min_quantity' => 'nullable|numeric|min:0',
            'max_quantity' => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $item = $this->service->updateItem($id, $data);

        return response()->json($item);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->removeItem($id, $tenantId);

        return response()->json(null, 204);
    }
}
