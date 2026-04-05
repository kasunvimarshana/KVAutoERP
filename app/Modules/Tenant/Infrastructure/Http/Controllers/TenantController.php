<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;

class TenantController extends Controller
{
    public function __construct(private readonly TenantServiceInterface $tenantService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->tenantService->list()->values());
    }

    public function show(int $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);

        if (! $tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        return response()->json($tenant);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->tenantService->create($request->all());

        return response()->json($tenant, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenantService->update($id, $request->all());

        return response()->json($tenant);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->tenantService->delete($id);

        return response()->json(['message' => 'Tenant deleted']);
    }
}
