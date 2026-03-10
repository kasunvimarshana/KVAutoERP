<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenants = $this->tenantRepository->all([], $request->only(['per_page', 'page', 'search', 'sort_by', 'sort_dir']));
        return response()->json(['success' => true, 'data' => $tenants]);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantRepository->create($request->validated());
        return response()->json(new TenantResource($tenant), 201);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantRepository->findById($id);
        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Tenant not found.'], 404);
        }
        return response()->json(new TenantResource($tenant));
    }

    public function update(UpdateTenantRequest $request, string $id): JsonResponse
    {
        $tenant = $this->tenantRepository->update($id, $request->validated());
        return response()->json(new TenantResource($tenant));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->tenantRepository->delete($id);
        return response()->json(['success' => true, 'message' => 'Tenant deleted.']);
    }
}
