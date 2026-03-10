<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

class TokenController extends Controller
{
    /**
     * Validate a bearer token - called by other microservices.
     * POST /api/tokens/validate
     * Authorization: Bearer <token>
     */
    public function validate(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->input('token');

        if (!$token) {
            return response()->json(['valid' => false, 'message' => 'No token provided.'], 401);
        }

        try {
            // If the token was passed in the request body rather than the Authorization header,
            // inject it so the Passport guard can read it from the current request.
            if (!$request->bearerToken() && $request->input('token')) {
                $request->headers->set('Authorization', 'Bearer ' . $request->input('token'));
            }

            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['valid' => false, 'message' => 'Invalid or expired token.'], 401);
            }

            return response()->json([
                'valid' => true,
                'user'  => new UserResource($user->load(['roles', 'permissions'])),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Token validation failed', ['error' => $e->getMessage()]);
            return response()->json(['valid' => false, 'message' => 'Token validation failed.'], 401);
        }
    }

    /**
     * Token introspection for authenticated user.
     * GET /api/tokens/introspect
     */
    public function introspect(Request $request): JsonResponse
    {
        $user  = $request->user();
        $token = $user->token();

        return response()->json([
            'active'      => true,
            'user_id'     => $user->id,
            'tenant_id'   => $user->tenant_id,
            'scopes'      => $token->scopes ?? [],
            'expires_at'  => $token->expires_at?->toIso8601String(),
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
