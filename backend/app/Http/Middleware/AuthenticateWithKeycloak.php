<?php

namespace App\Http\Middleware;

use App\Services\KeycloakService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithKeycloak
{
    public function __construct(private KeycloakService $keycloakService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized - No token provided'], 401);
        }

        try {
            $decoded = $this->keycloakService->validateToken($token);
            $request->merge(['auth_user' => $decoded]);
            $request->merge(['tenant_id' => $decoded->tenant_id ?? null]);
            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized - Invalid token: ' . $e->getMessage()], 401);
        }
    }
}
