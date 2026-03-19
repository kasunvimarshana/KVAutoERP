<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthContext;
use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\Contracts\Auth\TokenServiceInterface;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the Bearer JWT from the Authorization header.
 *
 * On success:
 *   - Stores decoded claims in `request->attributes->set('jwt_claims', [...])`.
 *   - Hydrates the AuthContext singleton with the verified claims.
 *
 * On failure:
 *   - Returns a 401 JSON error without calling the downstream handler.
 */
final class VerifyJwtMiddleware
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService,
        private readonly AuthContext $authContext,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractBearerToken($request);

        if ($token === null) {
            return ApiResponse::unauthorized('No authentication token provided.');
        }

        if (!$this->tokenService->verify($token)) {
            return ApiResponse::unauthorized('Token is invalid, expired, or has been revoked.');
        }

        $claims = $this->tokenService->decode($token);

        // Make claims available on the request for downstream use.
        $request->attributes->set('jwt_claims', $claims);
        $request->attributes->set('raw_token', $token);

        // Hydrate the request-scoped auth context.
        $this->authContext->hydrate($claims);

        return $next($request);
    }

    /**
     * Extract the raw token string from the Authorization: Bearer header.
     *
     * @param  Request  $request
     * @return string|null
     */
    private function extractBearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (!is_string($header) || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($header, 7));

        return $token !== '' ? $token : null;
    }
}
