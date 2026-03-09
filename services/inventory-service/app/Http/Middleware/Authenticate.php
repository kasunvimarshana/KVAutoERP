<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        try {
            $payload = $this->verifyToken($token);
            $request->merge(['_auth_user_id' => $payload['sub'] ?? null]);
            $request->merge(['_tenant_id'    => $payload['tenant_id'] ?? $request->header('X-Tenant-ID')]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Invalid or expired token.'], 401);
        }

        return $next($request);
    }

    private function verifyToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid JWT structure.');
        }
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) {
            throw new \InvalidArgumentException('Invalid JWT payload.');
        }
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \RuntimeException('Token expired.');
        }
        return $payload;
    }
}
