<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Tenant\Application\Contracts\CreateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\DeleteTenantServiceInterface;
use Modules\Tenant\Application\Contracts\FindTenantServiceInterface;
use Modules\Tenant\Application\Contracts\UpdateTenantConfigServiceInterface;
use Modules\Tenant\Application\Contracts\UpdateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\UploadTenantAttachmentServiceInterface;
use Modules\Tenant\Application\DTOs\TenantConfigData;
use Modules\Tenant\Application\DTOs\TenantData;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Infrastructure\Http\Requests\StoreTenantRequest;
use Modules\Tenant\Infrastructure\Http\Requests\UpdateTenantConfigRequest;
use Modules\Tenant\Infrastructure\Http\Requests\UpdateTenantRequest;
use Modules\Tenant\Infrastructure\Http\Resources\TenantCollection;
use Modules\Tenant\Infrastructure\Http\Resources\TenantConfigResource;
use Modules\Tenant\Infrastructure\Http\Resources\TenantResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantController extends AuthorizedController
{
    public function __construct(
        protected CreateTenantServiceInterface $createService,
        protected UpdateTenantServiceInterface $updateService,
        protected DeleteTenantServiceInterface $deleteService,
        protected UpdateTenantConfigServiceInterface $configService,
        protected FindTenantServiceInterface $findTenantService,
        protected UploadTenantAttachmentServiceInterface $uploadAttachmentService
    ) {}

    public function index(Request $request): TenantCollection
    {
        $this->authorize('viewAny', Tenant::class);
        $filters = $request->only(['name', 'slug', 'domain', 'active', 'status']);

        if ($request->has('active')) {
            $filters['active'] = $request->boolean('active');
        }

        $perPage = $request->integer('per_page', 15);
        $page    = $request->integer('page', 1);
        $sort    = $request->input('sort');
        $include = $request->input('include');

        $tenants = $this->findTenantService->list($filters, $perPage, $page, $sort, $include);

        return new TenantCollection($tenants);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $this->authorize('create', Tenant::class);
        $dto = TenantData::fromArray($request->validated());
        $tenant = $this->createService->execute($dto->toArray());

        if ($request->hasFile('logo')) {
            $this->uploadAttachmentService->execute([
                'tenant_id' => $tenant->getId(),
                'file'      => $request->file('logo'),
                'type'      => 'logo',
            ]);
            $tenant = $this->findTenantService->find($tenant->getId()) ?? $tenant;
        }

        return (new TenantResource($tenant))->response()->setStatusCode(201);
    }

    public function show(int $tenant): TenantResource
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('view', $tenantEntity);

        return new TenantResource($tenantEntity);
    }

    public function update(UpdateTenantRequest $request, int $tenant): TenantResource
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('update', $tenantEntity);

        $validated = $request->validated();
        $validated['id'] = $tenant;
        $dto = TenantData::fromArray($validated);
        $updated = $this->updateService->execute($dto->toArray());

        if ($request->hasFile('logo')) {
            $this->uploadAttachmentService->execute([
                'tenant_id' => $tenant,
                'file'      => $request->file('logo'),
                'type'      => 'logo',
            ]);

            $updated = $this->findTenantService->find($tenant) ?? $updated;
        }

        return new TenantResource($updated);
    }

    public function updateConfig(UpdateTenantConfigRequest $request, int $tenant): TenantConfigResource
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('updateConfig', $tenantEntity);

        $validated = $request->validated();
        $validated['id'] = $tenant;
        $dto = TenantConfigData::fromArray($validated);
        $updated = $this->configService->execute($dto->toArray());

        return new TenantConfigResource($updated);
    }

    public function destroy(int $tenant): JsonResponse
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('delete', $tenantEntity);
        $this->deleteService->execute(['id' => $tenant]);

        return Response::json(['message' => 'Tenant deleted successfully']);
    }

    public function configByDomain(string $domain): TenantConfigResource
    {
        $tenant = $this->findTenantService->findByDomain($domain);
        if (! $tenant) {
            throw new NotFoundHttpException('Tenant not found for the requested domain.');
        }

        return new TenantConfigResource($tenant);
    }

    private function findTenantOrFail(int $tenantId): Tenant
    {
        $tenant = $this->findTenantService->find($tenantId);
        if (! $tenant) {
            throw new NotFoundHttpException('Tenant not found.');
        }

        return $tenant;
    }

}
