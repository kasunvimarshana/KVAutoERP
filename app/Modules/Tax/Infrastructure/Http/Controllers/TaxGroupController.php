<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Infrastructure\Http\Controllers\BaseController;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Application\Contracts\TaxGroupRateServiceInterface;
use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;

class TaxGroupController extends BaseController
{
    public function __construct(
        private readonly TaxGroupServiceInterface $service,
        private readonly TaxGroupRateServiceInterface $rateService,
        private readonly CalculateTaxServiceInterface $calculateService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $groups = $this->service->listAll($tenantId);

        return response()->json(['data' => array_map(fn ($g) => $this->groupToArray($g), $groups)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'   => 'required|integer',
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_compound' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        $group = $this->service->create($validated);

        return response()->json(['data' => $this->groupToArray($group)], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $group = $this->service->findById($id, $tenantId);

        if ($group === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['data' => $this->groupToArray($group)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'code'        => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'is_compound' => 'sometimes|boolean',
            'is_active'   => 'sometimes|boolean',
        ]);

        $group = $this->service->update($id, $validated);

        return response()->json(['data' => $this->groupToArray($group)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(null, 204);
    }

    public function addRate(Request $request, int $taxGroupId): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'   => 'required|integer',
            'name'        => 'required|string|max:255',
            'rate'        => 'required|numeric|min:0',
            'order'       => 'integer|min:0',
            'is_compound' => 'boolean',
        ]);

        $rate = $this->rateService->addRate($taxGroupId, $validated);

        return response()->json(['data' => [
            'id'           => $rate->getId(),
            'tax_group_id' => $rate->getTaxGroupId(),
            'name'         => $rate->getName(),
            'rate'         => $rate->getRate(),
            'order'        => $rate->getOrder(),
            'is_compound'  => $rate->isCompound(),
        ]], 201);
    }

    public function removeRate(int $id): JsonResponse
    {
        $this->rateService->removeRate($id);

        return response()->json(null, 204);
    }

    public function calculate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'amount'    => 'required|numeric|min:0',
            'tenant_id' => 'required|integer',
        ]);

        $result = $this->calculateService->calculate(
            (float) $validated['amount'],
            $id,
            (int) $validated['tenant_id']
        );

        return response()->json(['data' => $result]);
    }

    private function groupToArray(mixed $g): array
    {
        return [
            'id'          => $g->getId(),
            'name'        => $g->getName(),
            'code'        => $g->getCode(),
            'description' => $g->getDescription(),
            'is_compound' => $g->isCompound(),
            'is_active'   => $g->isActive(),
        ];
    }
}
