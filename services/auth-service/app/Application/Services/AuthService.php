<?php

namespace App\Application\Services;

use App\Domain\Auth\Entities\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthService
{
    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    /**
     * Authenticate and issue a Passport access token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])
            ->forTenant($credentials['tenant_id'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact support.'],
            ]);
        }

        // Revoke all prior tokens to enforce single-session per user (optional)
        // $user->tokens()->delete();

        $tokenResult = $this->createPassportToken($user, 'personal-access');

        $user->update(['last_login_at' => now()]);

        Log::info("User [{$user->id}] logged in for tenant [{$user->tenant_id}]");

        return [
            'token_type'   => 'Bearer',
            'access_token' => $tokenResult->accessToken,
            'expires_at'   => $tokenResult->token->expires_at?->toIso8601String(),
            'user'         => $user->load('roles', 'permissions'),
        ];
    }

    /**
     * Revoke the current user's token.
     */
    public function logout(User $user): void
    {
        $user->token()->revoke();
        Log::info("User [{$user->id}] logged out");
    }

    /**
     * Issue a fresh token (simple re-issue via personal access token).
     */
    public function refresh(User $user): array
    {
        // Revoke old token
        $user->token()->revoke();

        $tokenResult = $this->createPassportToken($user, 'refresh');

        return [
            'token_type'   => 'Bearer',
            'access_token' => $tokenResult->accessToken,
            'expires_at'   => $tokenResult->token->expires_at?->toIso8601String(),
        ];
    }

    /**
     * Return the authenticated user with roles/permissions.
     */
    public function me(User $user): User
    {
        return $user->load('roles', 'permissions');
    }

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    /**
     * Register a new user within a tenant context.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'tenant_id'  => $data['tenant_id'],
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'is_active'  => true,
        ]);

        // Assign default role for new registrations
        $defaultRole = $data['role'] ?? 'viewer';
        if (in_array($defaultRole, ['viewer', 'staff'], true)) {
            $user->assignRole($defaultRole);
        }

        Log::info("New user [{$user->id}] registered for tenant [{$user->tenant_id}]");

        $tokenResult = $this->createPassportToken($user, 'registration');

        return [
            'token_type'   => 'Bearer',
            'access_token' => $tokenResult->accessToken,
            'expires_at'   => $tokenResult->token->expires_at?->toIso8601String(),
            'user'         => $user->load('roles', 'permissions'),
        ];
    }

    // -------------------------------------------------------------------------
    // Token verification
    // -------------------------------------------------------------------------

    /**
     * Verify a raw bearer token and return the associated user.
     *
     * @throws \RuntimeException
     */
    public function verifyToken(string $rawToken): array
    {
        // Use Passport's built-in guard to resolve the token
        $user = Auth::guard('api')->setToken($rawToken)->user();

        if (!$user) {
            throw new \RuntimeException('Invalid or expired token', 401);
        }

        return [
            'valid'       => true,
            'user_id'     => $user->id,
            'tenant_id'   => $user->tenant_id,
            'email'       => $user->email,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'claims'      => $user->getCustomClaims(),
        ];
    }

    // -------------------------------------------------------------------------
    // SSO
    // -------------------------------------------------------------------------

    /**
     * Issue a short-lived SSO token for cross-service authentication.
     */
    public function ssoToken(User $user): array
    {
        $tokenResult = $this->createPassportToken($user, 'sso', now()->addMinutes(5));

        Log::info("SSO token issued for user [{$user->id}] tenant [{$user->tenant_id}]");

        return [
            'sso_token'  => $tokenResult->accessToken,
            'expires_at' => $tokenResult->token->expires_at?->toIso8601String(),
            'claims'     => $user->getCustomClaims(),
        ];
    }

    /**
     * Validate an SSO token and return its decoded claims.
     */
    public function validateSsoToken(string $token): array
    {
        return $this->verifyToken($token);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function createPassportToken(User $user, string $tokenName, ?\Carbon\Carbon $expiresAt = null): PersonalAccessTokenResult
    {
        $scopes      = $user->getAllPermissions()->pluck('name')->toArray();
        $expiresAt ??= now()->addMinutes(config('passport.token_expire_in', 60));

        $tokenResult = $user->createToken($tokenName, $scopes, $expiresAt);

        return $tokenResult;
    }
}
