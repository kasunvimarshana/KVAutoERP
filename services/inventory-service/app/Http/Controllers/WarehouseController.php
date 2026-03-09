<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Inventory\Repositories\WarehouseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    public function __construct(private readonly WarehouseRepositoryInterface $warehouseRepository) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId   = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $warehouses = $this->warehouseRepository->findByTenant($tenantId);
        return response()->json(['data' => $warehouses]);
    }

    public function show(string $id): JsonResponse
    {
        $data = $this->warehouseRepository->findById($id);
        if (!$data) {
            return response()->json(['error' => 'Warehouse not found.'], 404);
        }
        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:50',
            'address'  => 'nullable|array',
            'capacity' => 'nullable|integer|min:1',
        ]);
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $data = $this->warehouseRepository->create([
            'id'        => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'name'      => $validated['name'],
            'code'      => strtoupper($validated['code']),
            'address'   => $validated['address'] ?? [],
            'is_active' => true,
            'capacity'  => $validated['capacity'] ?? null,
        ]);
        return response()->json(['data' => $data], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'address'   => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
            'capacity'  => 'sometimes|nullable|integer|min:1',
        ]);
        $data = $this->warehouseRepository->update($id, $validated);
        return response()->json(['data' => $data]);
    }
}
