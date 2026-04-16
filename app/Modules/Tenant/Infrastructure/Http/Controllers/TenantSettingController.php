<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Response;
use Modules\Tenant\Application\Contracts\CreateTenantSettingServiceInterface;
use Modules\Tenant\Application\Contracts\DeleteTenantSettingServiceInterface;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Tenant\Application\Contracts\FindTenantServiceInterface;
use Modules\Tenant\Application\Contracts\FindTenantSettingsServiceInterface;
use Modules\Tenant\Application\Contracts\UpdateTenantSettingServiceInterface;
use Modules\Tenant\Application\DTOs\TenantSettingData;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Infrastructure\Http\Requests\StoreTenantSettingRequest;
use Modules\Tenant\Infrastructure\Http\Requests\UpdateTenantSettingRequest;
use Modules\Tenant\Infrastructure\Http\Resources\TenantSettingResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantSettingController extends AuthorizedController
{
    public function __construct(
        private readonly FindTenantServiceInterface $findTenantService,
        private readonly FindTenantSettingsServiceInterface $findSettingsService,
        private readonly CreateTenantSettingServiceInterface $createSettingService,
        private readonly UpdateTenantSettingServiceInterface $updateSettingService,
        private readonly DeleteTenantSettingServiceInterface $deleteSettingService
    ) {}

    public function index(int $tenant, Request $request): AnonymousResourceCollection
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('view', $tenantEntity);
        $group = $request->query('group');
        $isPublicInput = $request->query('is_public');
        $isPublic = $isPublicInput === null ? null : filter_var($isPublicInput, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $settings = $this->findSettingsService->listByTenant(
            $tenant,
            is_string($group) ? $group : null,
            $isPublic
        );

        return TenantSettingResource::collection($settings);
    }

    public function show(int $tenant, string $key): TenantSettingResource
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('view', $tenantEntity);
        $setting = $this->findSettingsService->findByTenantAndKey($tenant, $key);

        if (! $setting) {
            throw new NotFoundHttpException('Tenant setting not found.');
        }

        return new TenantSettingResource($setting);
    }

    public function store(int $tenant, StoreTenantSettingRequest $request): JsonResponse
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('updateConfig', $tenantEntity);
        $validated = $request->validated();
        $validated['tenant_id'] = $tenant;
        $dto = TenantSettingData::fromArray($validated);
        $created = $this->createSettingService->execute($dto->toArray());

        return (new TenantSettingResource($created))->response()->setStatusCode(201);
    }

    public function update(int $tenant, string $key, UpdateTenantSettingRequest $request): TenantSettingResource
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('updateConfig', $tenantEntity);

        $existing = $this->findSettingsService->findByTenantAndKey($tenant, $key);
        if (! $existing) {
            throw new NotFoundHttpException('Tenant setting not found.');
        }

        $payload = $request->validated();
        $payload['tenant_id'] = $tenant;
        $payload['key'] = $key;
        $updated = $this->updateSettingService->execute($payload);

        return new TenantSettingResource($updated);
    }

    public function destroy(int $tenant, string $key): JsonResponse
    {
        $tenantEntity = $this->findTenantOrFail($tenant);
        $this->authorize('updateConfig', $tenantEntity);

        $existing = $this->findSettingsService->findByTenantAndKey($tenant, $key);
        if (! $existing) {
            throw new NotFoundHttpException('Tenant setting not found.');
        }

        $this->deleteSettingService->execute([
            'tenant_id' => $tenant,
            'key' => $key,
        ]);

        return Response::json(['message' => 'Tenant setting deleted successfully']);
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
