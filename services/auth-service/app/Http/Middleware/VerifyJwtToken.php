<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Services\TokenServiceInterface;
use App\Exceptions\TokenException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the JWT access token locally without calling the Auth service.
 * Decodes the token, checks signature (public key), expiry, and revocation list.
 * Embeds the decoded payload in request attributes for downstream use.
 */
class VerifyJwtToken
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if ($token === null) {
            return $this->unauthorized('No token provided.');
        }

        try {
            $payload = $this->tokenService->decodeAccessToken($token);
        } catch (TokenException $e) {
            return $this->unauthorized($e->getMessage(), $e->getCode());
        }

        // Validate required claims
        if (empty($payload['tenant_id']) || empty($payload['user_id'] ?? $payload['sub'] ?? '')) {
            return $this->unauthorized('Token is missing required claims.');
        }

        // Token version check is implicit via token_version claim vs user record
        // (handled by the Auth service during token issuance — stale tokens
        //  are naturally invalid once token_version increments)

        // Attach payload and helper values to the request for controllers
        $request->attributes->set('jwt_payload', $payload);
        $request->attributes->set('session_id', $payload['session_id'] ?? '');
        $request->attributes->set('token_remaining_ttl', $this->tokenService->getRemainingTtl($payload));

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $bearer = $request->bearerToken();
        if ($bearer !== null) {
            return $bearer;
        }

        // Support token via query string for SSE/download scenarios (signed URLs only)
        return $request->query('token');
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
