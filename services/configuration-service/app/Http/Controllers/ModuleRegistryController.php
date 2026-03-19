<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\ModuleRegistryServiceInterface;
use App\DTOs\ModuleRegistryDto;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\CreateModuleRegistryRequest;
use App\Http\Requests\UpdateModuleRegistryRequest;
use App\Http\Resources\ModuleRegistryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ModuleRegistryController extends Controller
{
    public function __construct(
        private readonly ModuleRegistryServiceInterface $moduleService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->query('tenant_id', '');
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->moduleService->listForTenant($tenantId, $perPage);

        return response()->json([
            'success' => true,
            'data'    => ModuleRegistryResource::collection($paginator->items()),
            'message' => 'Modules retrieved successfully.',
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ]);
    }

    public function store(CreateModuleRegistryRequest $request): JsonResponse
    {
        try {
            $dto = ModuleRegistryDto::fromArray($request->validated());
            $module = $this->moduleService->create($dto);

            return response()->json([
                'success' => true,
                'data'    => new ModuleRegistryResource($module),
                'message' => 'Module registered successfully.',
            ], 201);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $module = $this->moduleService->findById($id);

            return response()->json([
                'success' => true,
                'data'    => new ModuleRegistryResource($module),
                'message' => 'Module retrieved successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 404);
        }
    }

    public function update(UpdateModuleRegistryRequest $request, string $id): JsonResponse
    {
        try {
            $existing = $this->moduleService->findById($id);
            $dto = ModuleRegistryDto::fromArray(
                array_merge($existing->toArray(), $request->validated()),
            );
            $module = $this->moduleService->update($id, $dto);

            return response()->json([
                'success' => true,
                'data'    => new ModuleRegistryResource($module),
                'message' => 'Module updated successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->moduleService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/modules/{id}/toggle",
     *     summary="Enable or disable a module for a tenant",
     *     tags={"ModuleRegistry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Module toggled")
     * )
     */
    public function toggle(string $id): JsonResponse
    {
        try {
            $module = $this->moduleService->toggle($id);

            return response()->json([
                'success' => true,
                'data'    => new ModuleRegistryResource($module),
                'message' => 'Module ' . ($module->is_enabled ? 'enabled' : 'disabled') . ' successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }
}
