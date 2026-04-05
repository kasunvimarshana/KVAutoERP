<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;

class TaxGroupRateController extends Controller
{
    public function __construct(
        private readonly TaxGroupServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId    = (int) $request->header('X-Tenant-ID', 0);
        $taxGroupId  = (int) $request->query('tax_group_id', 0);

        if ($taxGroupId <= 0) {
            return response()->json(['message' => 'Provide tax_group_id query parameter.'], 422);
        }

        return response()->json($this->service->getRates($taxGroupId, $tenantId));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tax_group_id' => 'required|integer',
            'tax_rate_id'  => 'required|integer',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        $groupRate = $this->service->addRate(
            (int) $data['tax_group_id'],
            (int) $data['tax_rate_id'],
            (int) ($data['sort_order'] ?? 0),
            $tenantId,
        );

        return response()->json($groupRate, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->removeRate($id, $tenantId);

        return response()->json(null, 204);
    }
}
