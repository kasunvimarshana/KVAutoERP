<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pricing\Application\Contracts\DiscountServiceInterface;

class DiscountController extends Controller
{
    public function __construct(
        private readonly DiscountServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->allByTenant($tenantId));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:100',
            'type'             => 'required|in:percentage,fixed',
            'value'            => 'required|numeric|min:0.0001',
            'applies_to_type'  => 'required|in:product,category,order',
            'applies_to_id'    => 'nullable|integer',
            'min_order_amount' => 'nullable|numeric|min:0',
            'valid_from'       => 'nullable|date',
            'valid_to'         => 'nullable|date|after_or_equal:valid_from',
            'is_active'        => 'nullable|boolean',
            'usage_limit'      => 'nullable|integer|min:1',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $discount = $this->service->createDiscount($data);

        return response()->json($discount, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->findById($id, $tenantId));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'code'             => 'sometimes|string|max:100',
            'type'             => 'sometimes|in:percentage,fixed',
            'value'            => 'sometimes|numeric|min:0.0001',
            'applies_to_type'  => 'sometimes|in:product,category,order',
            'applies_to_id'    => 'nullable|integer',
            'min_order_amount' => 'nullable|numeric|min:0',
            'valid_from'       => 'nullable|date',
            'valid_to'         => 'nullable|date|after_or_equal:valid_from',
            'is_active'        => 'nullable|boolean',
            'usage_limit'      => 'nullable|integer|min:1',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $discount = $this->service->updateDiscount($id, $data);

        return response()->json($discount);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->deleteDiscount($id, $tenantId);

        return response()->json(null, 204);
    }

    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'   => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        $finalAmount = $this->service->applyDiscount(
            (string) $data['code'],
            (float) $data['amount'],
            $tenantId,
        );

        return response()->json(['final_amount' => $finalAmount]);
    }
}
