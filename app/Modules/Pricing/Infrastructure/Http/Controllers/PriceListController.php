<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pricing\Application\Contracts\PriceListServiceInterface;

class PriceListController extends Controller
{
    public function __construct(
        private readonly PriceListServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->allByTenant($tenantId));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100',
            'currency'    => 'nullable|string|size:3',
            'is_default'  => 'nullable|boolean',
            'valid_from'  => 'nullable|date',
            'valid_to'    => 'nullable|date|after_or_equal:valid_from',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $priceList = $this->service->create($data);

        return response()->json($priceList, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->findById($id, $tenantId));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'code'        => 'sometimes|string|max:100',
            'currency'    => 'nullable|string|size:3',
            'is_default'  => 'nullable|boolean',
            'valid_from'  => 'nullable|date',
            'valid_to'    => 'nullable|date|after_or_equal:valid_from',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $priceList = $this->service->update($id, $data);

        return response()->json($priceList);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->delete($id, $tenantId);

        return response()->json(null, 204);
    }
}
