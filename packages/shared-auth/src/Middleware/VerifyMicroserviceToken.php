<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Middleware;

use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedAuth\Contracts\JwtVerifierInterface;
use KvEnterprise\SharedAuth\Contracts\TenantContextInterface;
use KvEnterprise\SharedAuth\Exceptions\TokenVerificationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Drop-in middleware for any microservice to verify JWT tokens locally.
 *
 * Install in any Laravel microservice:
 *   1. Require kv-enterprise/shared-auth via composer
 *   2. Add KvEnterprise\SharedAuth\Providers\SharedAuthServiceProvider to config/app.php
 *   3. Apply this middleware to protected routes
 *
 * The middleware:
 *   - Extracts the Bearer token from the Authorization header
 *   - Verifies the signature using the Auth service's public key (local)
 *   - Checks the JTI against the Redis revocation list
 *   - Populates TenantContext for downstream use
 *   - Stores the decoded payload in request attributes
 */
class VerifyMicroserviceToken
{
    public function __construct(
        private readonly JwtVerifierInterface $jwtVerifier,
        private readonly TenantContextInterface $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return $this->unauthorized('No authentication token provided.');
        }

        try {
            $payload = $this->jwtVerifier->verify($token);
        } catch (TokenVerificationException $e) {
            return $this->unauthorized($e->getMessage(), $e->getCode());
        }

        // Populate the request-scoped tenant context
        $this->tenantContext->setFromPayload($payload);

        // Also store in request attributes for controllers that prefer direct access
        $request->attributes->set('jwt_payload', $payload);
        $request->attributes->set('tenant_id', $payload['tenant_id'] ?? '');
        $request->attributes->set('user_id', $payload['user_id'] ?? $payload['sub'] ?? '');

        return $next($request);
    }

    private function unauthorized(string $message, int $code = 401): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => 'UNAUTHORIZED',
        ], 401);
    }
}
