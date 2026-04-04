<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tenant\Application\Contracts\ActivateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\CreateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\DeleteTenantServiceInterface;
use Modules\Tenant\Application\Contracts\GetTenantServiceInterface;
use Modules\Tenant\Application\Contracts\ListTenantsServiceInterface;
use Modules\Tenant\Application\Contracts\SuspendTenantServiceInterface;
use Modules\Tenant\Application\Contracts\UpdateTenantServiceInterface;
use Modules\Tenant\Application\DTOs\CreateTenantData;
use Modules\Tenant\Application\DTOs\UpdateTenantData;
use Modules\Tenant\Infrastructure\Http\Requests\CreateTenantRequest;
use Modules\Tenant\Infrastructure\Http\Requests\UpdateTenantRequest;
use Modules\Tenant\Infrastructure\Http\Resources\TenantResource;

class TenantController extends Controller
{
    public function __construct(
        private readonly CreateTenantServiceInterface $createService,
        private readonly UpdateTenantServiceInterface $updateService,
        private readonly DeleteTenantServiceInterface $deleteService,
        private readonly GetTenantServiceInterface $getService,
        private readonly ListTenantsServiceInterface $listService,
        private readonly SuspendTenantServiceInterface $suspendService,
        private readonly ActivateTenantServiceInterface $activateService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenants = $this->listService->execute(
            filters: $request->only(['status']),
            perPage: (int) $request->get('per_page', 15),
            page: (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => TenantResource::collection($tenants->items()),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page'    => $tenants->lastPage(),
                'per_page'     => $tenants->perPage(),
                'total'        => $tenants->total(),
            ],
        ]);
    }

    public function store(CreateTenantRequest $request): JsonResponse
    {
        $data = CreateTenantData::fromArray($request->validated());
        $data->createdBy = $request->user()?->id;

        $tenant = $this->createService->execute($data);

        return response()->json(new TenantResource($tenant), 201);
    }

    public function show(int $id): JsonResponse
    {
        $tenant = $this->getService->execute($id);

        return response()->json(new TenantResource($tenant));
    }

    public function update(UpdateTenantRequest $request, int $id): JsonResponse
    {
        $data = UpdateTenantData::fromArray($request->validated());
        $data->updatedBy = $request->user()?->id;

        $tenant = $this->updateService->execute($id, $data);

        return response()->json(new TenantResource($tenant));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }

    public function suspend(int $id): JsonResponse
    {
        $tenant = $this->suspendService->execute($id);

        return response()->json(new TenantResource($tenant));
    }

    public function activate(int $id): JsonResponse
    {
        $tenant = $this->activateService->execute($id);

        return response()->json(new TenantResource($tenant));
    }
}
