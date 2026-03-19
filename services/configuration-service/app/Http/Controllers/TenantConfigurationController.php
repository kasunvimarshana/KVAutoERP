<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\TenantConfigServiceInterface;
use App\DTOs\TenantConfigurationDto;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\CreateTenantConfigurationRequest;
use App\Http\Requests\UpdateTenantConfigurationRequest;
use App\Http\Resources\TenantConfigurationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TenantConfigurationController extends Controller
{
    public function __construct(
        private readonly TenantConfigServiceInterface $configService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/config",
     *     summary="List tenant configurations (paginated)",
     *     tags={"TenantConfiguration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of configurations")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->query('tenant_id', '');
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->configService->listForTenant($tenantId, $perPage);

        return response()->json([
            'success' => true,
            'data'    => TenantConfigurationResource::collection($paginator->items()),
            'message' => 'Configurations retrieved successfully.',
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/{tenantId}/{service}",
     *     summary="Get all active configurations for a service as a key-value map",
     *     tags={"TenantConfiguration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Service configuration map")
     * )
     */
    public function getServiceConfig(string $tenantId, string $service): JsonResponse
    {
        $config = $this->configService->getServiceConfig($tenantId, $service);

        return response()->json([
            'success' => true,
            'data'    => $config,
            'message' => "Configuration for service '{$service}' retrieved successfully.",
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/config",
     *     summary="Create a new tenant configuration",
     *     tags={"TenantConfiguration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Configuration created")
     * )
     */
    public function store(CreateTenantConfigurationRequest $request): JsonResponse
    {
        try {
            $dto = TenantConfigurationDto::fromArray($request->validated());
            $config = $this->configService->create($dto);

            return response()->json([
                'success' => true,
                'data'    => new TenantConfigurationResource($config),
                'message' => 'Configuration created successfully.',
            ], 201);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/{id}",
     *     summary="Get a single configuration by ID",
     *     tags={"TenantConfiguration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Configuration details")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $config = $this->configService->findById($id);

            return response()->json([
                'success' => true,
                'data'    => new TenantConfigurationResource($config),
                'message' => 'Configuration retrieved successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/config/{id}",
     *     summary="Update a tenant configuration",
     *     tags={"TenantConfiguration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Configuration updated")
     * )
     */
    public function update(UpdateTenantConfigurationRequest $request, string $id): JsonResponse
    {
        try {
            $existing = $this->configService->findById($id);
            $dto = TenantConfigurationDto::fromArray(
                array_merge($existing->toArray(), $request->validated()),
            );
            $config = $this->configService->update($id, $dto);

            return response()->json([
                'success' => true,
                'data'    => new TenantConfigurationResource($config),
                'message' => 'Configuration updated successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/config/{id}",
     *     summary="Delete a tenant configuration",
     *     tags={"TenantConfiguration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=204, description="Configuration deleted")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->configService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Configuration deleted successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }
}
