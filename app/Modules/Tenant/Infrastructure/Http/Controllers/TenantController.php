<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Infrastructure\Http\Resources\TenantResource;

class TenantController extends AuthorizedController
{
    public function __construct(
        private readonly TenantServiceInterface $service,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);
        $page    = (int) $request->query('page', 1);

        $tenants = $this->service->findAll($perPage, $page);

        return TenantResource::collection($tenants);
    }

    public function store(Request $request): TenantResource
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'slug'     => 'required|string|max:255|unique:tenants,slug',
            'domain'   => 'nullable|string|max:255|unique:tenants,domain',
            'email'    => 'required|email|max:255',
            'phone'    => 'nullable|string|max:50',
            'address'  => 'nullable|array',
            'plan_id'  => 'nullable|integer',
            'settings' => 'nullable|array',
        ]);

        $tenant = $this->service->create($validated);

        return new TenantResource($tenant);
    }

    public function show(int $id): TenantResource
    {
        $tenant = $this->service->find($id);

        return new TenantResource($tenant);
    }

    public function update(Request $request, int $id): TenantResource
    {
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'slug'      => 'sometimes|string|max:255|unique:tenants,slug,'.$id,
            'domain'    => 'nullable|string|max:255|unique:tenants,domain,'.$id,
            'email'     => 'sometimes|email|max:255',
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'plan_id'   => 'nullable|integer',
            'settings'  => 'nullable|array',
        ]);

        $tenant = $this->service->update($id, $validated);

        return new TenantResource($tenant);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(['message' => 'Tenant deleted successfully.']);
    }
}
