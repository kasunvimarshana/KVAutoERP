<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Configuration\Application\Contracts\GetSystemConfigServiceInterface;
use Modules\Configuration\Application\Contracts\UpdateSystemConfigServiceInterface;
use Modules\Configuration\Application\DTOs\UpdateSystemConfigData;
use Modules\Configuration\Domain\Repositories\SystemConfigRepositoryInterface;
use Modules\Configuration\Infrastructure\Http\Requests\UpdateSystemConfigRequest;
use Modules\Configuration\Infrastructure\Http\Resources\SystemConfigResource;

class SystemConfigController extends Controller
{
    public function __construct(
        private readonly GetSystemConfigServiceInterface $getService,
        private readonly UpdateSystemConfigServiceInterface $updateService,
        private readonly SystemConfigRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->has('tenant_id') ? (int) $request->get('tenant_id') : null;

        $configs = $this->repository->findAll(
            $tenantId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => SystemConfigResource::collection($configs->items()),
            'meta' => [
                'current_page' => $configs->currentPage(),
                'last_page'    => $configs->lastPage(),
                'per_page'     => $configs->perPage(),
                'total'        => $configs->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $config = $this->repository->findById($id);

        if ($config === null) {
            return response()->json(['message' => 'Config not found.'], 404);
        }

        return response()->json(new SystemConfigResource($config));
    }

    public function upsert(UpdateSystemConfigRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = UpdateSystemConfigData::fromArray([
            'key'         => $validated['key'],
            'value'       => $validated['value'] ?? null,
            'tenantId'    => isset($validated['tenant_id']) ? (int) $validated['tenant_id'] : null,
            'group'       => $validated['group'] ?? 'general',
            'description' => $validated['description'] ?? null,
        ]);

        $config = $this->updateService->execute($data);

        return response()->json(new SystemConfigResource($config));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json(null, 204);
    }
}
