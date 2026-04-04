<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Configuration\Application\Contracts\GetSettingGroupServiceInterface;
use Modules\Configuration\Application\Contracts\GetSettingServiceInterface;
use Modules\Configuration\Application\Contracts\SetSettingServiceInterface;
use Modules\Configuration\Application\DTOs\SetSettingData;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;

class SettingController extends AuthorizedController
{
    public function __construct(
        private readonly GetSettingServiceInterface $getService,
        private readonly SetSettingServiceInterface $setService,
        private readonly GetSettingGroupServiceInterface $groupService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->user()?->tenant_id ?? 0);

        /** @var \Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface $repo */
        $repo = app(\Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface::class);
        $settings = $repo->getAllByTenant($tenantId);

        return response()->json($settings);
    }

    public function group(Request $request, string $group): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->user()?->tenant_id ?? 0);
        $settings = $this->groupService->execute($tenantId, $group);

        return response()->json($settings);
    }

    public function show(Request $request, string $group, string $key): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->user()?->tenant_id ?? 0);
        $setting = $this->getService->execute($tenantId, $group, $key);

        return response()->json($setting);
    }

    public function update(Request $request, string $group, string $key): JsonResponse
    {
        $validated = $request->validate([
            'value'       => ['nullable'],
            'type'        => ['nullable', 'string', 'in:string,integer,float,boolean,json,array'],
            'description' => ['nullable', 'string'],
        ]);

        $tenantId = (int) $request->header('X-Tenant-ID', $request->user()?->tenant_id ?? 0);

        $data = new SetSettingData(
            tenantId: $tenantId,
            group: $group,
            key: $key,
            value: $validated['value'] ?? null,
            type: $validated['type'] ?? 'string',
        );

        $setting = $this->setService->execute($data);

        return response()->json($setting);
    }
}
