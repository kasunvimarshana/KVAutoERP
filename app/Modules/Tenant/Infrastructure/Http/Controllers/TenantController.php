<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
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

class TenantController extends AuthorizedController
{
    public function __construct(
        private readonly CreateTenantServiceInterface $createService,
        private readonly UpdateTenantServiceInterface $updateService,
        private readonly DeleteTenantServiceInterface $deleteService,
        private readonly GetTenantServiceInterface $getService,
        private readonly ListTenantsServiceInterface $listService,
        private readonly SuspendTenantServiceInterface $suspendService,
    ) {}

    public function index(): JsonResponse
    {
        $page = (int) request()->get('page', 1);
        $perPage = (int) request()->get('per_page', 15);
        $result = $this->listService->execute($page, $perPage);

        return response()->json($result);
    }

    public function show(int $id): JsonResponse
    {
        $tenant = $this->getService->execute($id);

        return (new TenantResource($tenant))->response();
    }

    public function store(CreateTenantRequest $request): JsonResponse
    {
        $data = new CreateTenantData(
            name: $request->input('name'),
            slug: $request->input('slug'),
            plan: $request->input('plan', 'starter'),
            locale: $request->input('locale', 'en'),
            timezone: $request->input('timezone', 'UTC'),
            currency: $request->input('currency', 'USD'),
        );

        $tenant = $this->createService->execute($data);

        return (new TenantResource($tenant))->response()->setStatusCode(201);
    }

    public function update(UpdateTenantRequest $request, int $id): JsonResponse
    {
        $data = new UpdateTenantData(
            name: $request->input('name'),
            slug: $request->input('slug'),
            plan: $request->input('plan'),
            locale: $request->input('locale'),
            timezone: $request->input('timezone'),
            currency: $request->input('currency'),
            status: $request->input('status'),
        );

        $tenant = $this->updateService->execute($id, $data);

        return (new TenantResource($tenant))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }

    public function suspend(int $id): JsonResponse
    {
        $tenant = $this->suspendService->execute($id);

        return (new TenantResource($tenant))->response();
    }
}
