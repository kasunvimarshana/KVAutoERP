<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Controllers;

use App\Modules\Tenant\Application\Services\TenantService;
use App\Modules\Tenant\Http\Requests\StoreTenantRequest;
use App\Modules\Tenant\Http\Requests\UpdateTenantRequest;
use App\Modules\Tenant\Http\Resources\TenantCollection;
use App\Modules\Tenant\Http\Resources\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TenantController
 *
 * Super-admin endpoint for tenant CRUD management.
 */
class TenantController
{
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->has('per_page') ? (int) $request->input('per_page') : null;
        $page    = max(1, (int) $request->input('page', 1));

        $tenants = $this->tenantService->list(
            filters: $request->only(['plan', 'is_active']),
            sort:    [$request->input('sort_by', 'created_at') => $request->input('sort_dir', 'desc')],
            perPage: $perPage,
            page:    $page
        );

        return response()->json([
            'success' => true,
            'data'    => new TenantCollection($tenants),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);

        return response()->json([
            'success' => true,
            'data'    => new TenantResource($tenant),
        ]);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully.',
            'data'    => new TenantResource($tenant),
        ], 201);
    }

    public function update(UpdateTenantRequest $request, int|string $id): JsonResponse
    {
        $tenant = $this->tenantService->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully.',
            'data'    => new TenantResource($tenant),
        ]);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $this->tenantService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully.',
        ]);
    }
}
