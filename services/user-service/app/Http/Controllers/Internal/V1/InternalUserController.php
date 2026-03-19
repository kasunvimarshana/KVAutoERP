<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal\V1;

use App\Contracts\Services\UserProfileServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserClaimsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Internal user controller (v1) — service-to-service only.
 *
 * Endpoints here are protected by VerifyServiceKeyMiddleware and are
 * not exposed to end-users. They are consumed by other microservices
 * (e.g. Auth Service enriching JWT tokens with user-domain claims).
 */
final class InternalUserController extends Controller
{
    public function __construct(
        private readonly UserProfileServiceInterface $userProfileService,
    ) {}

    /**
     * Return the JWT claims payload for a given auth_user_id and tenant.
     *
     * Called by the Auth Service when issuing or refreshing tokens so that
     * role/permission claims and profile data are always authoritative.
     *
     * @param  Request  $request
     * @param  string   $authUserId
     * @return JsonResponse
     */
    public function claims(Request $request, string $authUserId): JsonResponse
    {
        $tenantId = (string) $request->query('tenant_id', '');

        if ($tenantId === '') {
            return ApiResponse::error('The tenant_id query parameter is required.', [], 422);
        }

        $claimsData = $this->userProfileService->getClaimsForAuth($authUserId, $tenantId);

        if ($claimsData === null) {
            return ApiResponse::notFound('No user profile found for the given auth_user_id and tenant.');
        }

        return ApiResponse::success(new UserClaimsResource($claimsData), 'Claims retrieved successfully.');
    }
}
