<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\User\Entities\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Passport\Client;
use Laravel\Passport\PersonalAccessTokenResult;

/**
 * Authentication Service.
 *
 * Handles tenant-aware SSO authentication using Laravel Passport.
 * Supports multi-guard auth per user, device, and organization.
 */
class AuthService
{
    /**
     * Attempt to authenticate a user and issue an access token.
     *
     * @param  string               $email
     * @param  string               $password
     * @param  string               $tenantId
     * @param  array<string, mixed> $deviceInfo  Device metadata for token scoping
     * @return array<string, mixed>
     * @throws UnauthorizedException
     */
    public function login(
        string $email,
        string $password,
        string $tenantId,
        array $deviceInfo = [],
    ): array {
        $user = User::where('email', $email)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($user === null || !Hash::check($password, $user->password)) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        // Record login metadata
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        // Issue tenant-scoped access token with device info
        $tokenName    = $deviceInfo['device_name'] ?? 'API Access Token';
        $tokenScopes  = $this->resolveUserScopes($user);

        $tokenResult = $user->createToken($tokenName, $tokenScopes);

        return [
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $tokenResult->token->expires_at?->toISOString(),
            'scopes'       => $tokenScopes,
            'user'         => $user,
        ];
    }

    /**
     * Revoke the current user's access token (logout).
     *
     * @param  User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        return (bool) $user->token()?->revoke();
    }

    /**
     * Revoke all tokens for a user (logout all devices).
     *
     * @param  User $user
     * @return void
     */
    public function logoutAllDevices(User $user): void
    {
        $user->tokens()->each(fn ($token) => $token->revoke());
    }

    /**
     * Refresh an access token.
     *
     * @param  User $user
     * @return array<string, mixed>
     */
    public function refreshToken(User $user): array
    {
        $this->logout($user);

        $tokenScopes = $this->resolveUserScopes($user);
        $tokenResult = $user->createToken('Refreshed Token', $tokenScopes);

        return [
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $tokenResult->token->expires_at?->toISOString(),
            'scopes'       => $tokenScopes,
        ];
    }

    /**
     * Resolve the OAuth scopes for a user based on their roles.
     *
     * @param  User $user
     * @return string[]
     */
    private function resolveUserScopes(User $user): array
    {
        $scopes = ['*']; // Default full scope; can be refined per role

        foreach ($user->roles as $role) {
            $roleScopes = config("auth.role_scopes.{$role->name}", []);
            $scopes     = array_unique(array_merge($scopes, $roleScopes));
        }

        return $scopes;
    }
}
