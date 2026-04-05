<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;

class TaxGroupController extends Controller
{
    public function __construct(
        private readonly TaxGroupServiceInterface $service,
        private readonly CalculateTaxServiceInterface $calculateTaxService,
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
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $group = $this->service->create($data);

        return response()->json($group, 201);
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
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $group = $this->service->update($id, $data);

        return response()->json($group);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->delete($id, $tenantId);

        return response()->json(null, 204);
    }

    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tax_group_id' => 'required|integer',
            'amount'       => 'required|numeric|min:0',
        ]);

        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        $result = $this->calculateTaxService->calculate(
            (int) $data['tax_group_id'],
            (float) $data['amount'],
            $tenantId,
        );

        return response()->json($result);
    }
}
