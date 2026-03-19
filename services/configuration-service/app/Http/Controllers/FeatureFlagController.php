<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\FeatureFlagServiceInterface;
use App\DTOs\FeatureFlagDto;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\CreateFeatureFlagRequest;
use App\Http\Requests\UpdateFeatureFlagRequest;
use App\Http\Resources\FeatureFlagResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FeatureFlagController extends Controller
{
    public function __construct(
        private readonly FeatureFlagServiceInterface $flagService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/features",
     *     summary="List feature flags for a tenant (paginated)",
     *     tags={"FeatureFlags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of feature flags")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->query('tenant_id', '');
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->flagService->listForTenant($tenantId, $perPage);

        return response()->json([
            'success' => true,
            'data'    => FeatureFlagResource::collection($paginator->items()),
            'message' => 'Feature flags retrieved successfully.',
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/features",
     *     summary="Create a new feature flag",
     *     tags={"FeatureFlags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Feature flag created")
     * )
     */
    public function store(CreateFeatureFlagRequest $request): JsonResponse
    {
        try {
            $dto = FeatureFlagDto::fromArray($request->validated());
            $flag = $this->flagService->create($dto);

            return response()->json([
                'success' => true,
                'data'    => new FeatureFlagResource($flag),
                'message' => 'Feature flag created successfully.',
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
     *     path="/api/v1/features/{id}",
     *     summary="Get a single feature flag by ID",
     *     tags={"FeatureFlags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Feature flag details")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $flag = $this->flagService->findById($id);

            return response()->json([
                'success' => true,
                'data'    => new FeatureFlagResource($flag),
                'message' => 'Feature flag retrieved successfully.',
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
     *     path="/api/v1/features/{id}",
     *     summary="Update a feature flag",
     *     tags={"FeatureFlags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Feature flag updated")
     * )
     */
    public function update(UpdateFeatureFlagRequest $request, string $id): JsonResponse
    {
        try {
            $existing = $this->flagService->findById($id);
            $dto = FeatureFlagDto::fromArray(
                array_merge($existing->toArray(), $request->validated()),
            );
            $flag = $this->flagService->update($id, $dto);

            return response()->json([
                'success' => true,
                'data'    => new FeatureFlagResource($flag),
                'message' => 'Feature flag updated successfully.',
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
     *     path="/api/v1/features/{id}",
     *     summary="Delete a feature flag",
     *     tags={"FeatureFlags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Feature flag deleted")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->flagService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Feature flag deleted successfully.',
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
     *     path="/api/v1/features/{id}/toggle",
     *     summary="Toggle feature flag enabled state",
     *     tags={"FeatureFlags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Feature flag toggled")
     * )
     */
    public function toggle(string $id): JsonResponse
    {
        try {
            $flag = $this->flagService->toggle($id);

            return response()->json([
                'success' => true,
                'data'    => new FeatureFlagResource($flag),
                'message' => 'Feature flag ' . ($flag->is_enabled ? 'enabled' : 'disabled') . ' successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/features/check/{flagKey}",
     *     summary="Check if a feature flag is enabled for the authenticated tenant",
     *     tags={"FeatureFlags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Feature flag status")
     * )
     */
    public function check(Request $request, string $flagKey): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->query('tenant_id', '');
        $userId = $request->attributes->get('user_id');

        $context = array_filter(['user_id' => $userId]);
        $isEnabled = $this->flagService->isEnabled($tenantId, $flagKey, $context);

        return response()->json([
            'success' => true,
            'data'    => [
                'flag_key'   => $flagKey,
                'tenant_id'  => $tenantId,
                'is_enabled' => $isEnabled,
            ],
            'message' => "Feature flag '{$flagKey}' is " . ($isEnabled ? 'enabled' : 'disabled') . '.',
        ]);
    }
}
