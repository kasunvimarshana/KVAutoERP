<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tax\Application\Contracts\TaxRateServiceInterface;

class TaxRateController extends Controller
{
    public function __construct(
        private readonly TaxRateServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        if ($request->filled('country')) {
            return response()->json(
                $this->service->getByCountry((string) $request->query('country'), $tenantId)
            );
        }

        return response()->json($this->service->allByTenant($tenantId));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100',
            'rate'        => 'required|numeric|min:0',
            'type'        => 'required|in:percentage,fixed',
            'is_compound' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'country'     => 'nullable|string|size:3',
            'region'      => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $taxRate = $this->service->create($data);

        return response()->json($taxRate, 201);
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
            'rate'        => 'sometimes|numeric|min:0',
            'type'        => 'sometimes|in:percentage,fixed',
            'is_compound' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'country'     => 'nullable|string|size:3',
            'region'      => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $taxRate = $this->service->update($id, $data);

        return response()->json($taxRate);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->delete($id, $tenantId);

        return response()->json(null, 204);
    }
}
