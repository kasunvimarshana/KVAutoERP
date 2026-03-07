<?php

namespace App\Http\Controllers;

use App\Application\Services\TenantService;
use App\Http\Requests\CreateTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantController extends Controller
{
    public function __construct(private TenantService $tenantService) {}

    /**
     * List all tenants with optional filters and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $params = $request->only(['search', 'status', 'per_page', 'page']);
        $tenants = $this->tenantService->listTenants($params);

        return TenantResource::collection($tenants);
    }

    /**
     * Show a single tenant.
     */
    public function show(string $id): TenantResource|JsonResponse
    {
        try {
            $tenant = $this->tenantService->getTenant($id);
            return new TenantResource($tenant);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Create a new tenant.
     */
    public function store(CreateTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->createTenant($request->validated());

        return (new TenantResource($tenant))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing tenant.
     */
    public function update(UpdateTenantRequest $request, string $id): TenantResource|JsonResponse
    {
        try {
            $tenant = $this->tenantService->updateTenant($id, $request->validated());
            return new TenantResource($tenant);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Soft-delete a tenant.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->tenantService->deleteTenant($id);
            return response()->json(['message' => 'Tenant deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Activate a suspended or inactive tenant.
     */
    public function activate(string $id): TenantResource|JsonResponse
    {
        try {
            $tenant = $this->tenantService->activateTenant($id);
            return new TenantResource($tenant);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Suspend an active tenant.
     */
    public function suspend(string $id): TenantResource|JsonResponse
    {
        try {
            $tenant = $this->tenantService->suspendTenant($id);
            return new TenantResource($tenant);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Retrieve the current (cached) config for a tenant.
     */
    public function getConfig(string $id): JsonResponse
    {
        try {
            $config = $this->tenantService->getTenantConfig($id);
            // Strip sensitive fields before returning
            unset($config['database'], $config['mail'], $config['broker']);
            return response()->json(['data' => $config]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Update persisted config fields (settings, cache_config, etc.).
     */
    public function updateConfig(Request $request, string $id): TenantResource|JsonResponse
    {
        $request->validate([
            'settings'      => 'sometimes|array',
            'cache_config'  => 'sometimes|array',
            'mail_config'   => 'sometimes|array',
            'broker_config' => 'sometimes|array',
            'db_config'     => 'sometimes|array',
        ]);

        try {
            $tenant = $this->tenantService->updateTenantConfig($id, $request->all());
            return new TenantResource($tenant);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Force-refresh the cached config for a tenant.
     */
    public function refreshConfig(string $id): JsonResponse
    {
        try {
            $this->tenantService->refreshTenantConfig($id);
            return response()->json(['message' => 'Tenant config refreshed successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
    }

    /**
     * Internal endpoint: find tenant by custom domain.
     */
    public function findByDomain(string $domain): TenantResource|JsonResponse
    {
        $tenant = $this->tenantService->findByDomain($domain);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found for domain'], 404);
        }

        return new TenantResource($tenant);
    }
}
