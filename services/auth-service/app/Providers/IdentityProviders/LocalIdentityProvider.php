<?php

declare(strict_types=1);

namespace App\Providers\IdentityProviders;

use App\Contracts\IdentityProviderContract;
use App\Contracts\TokenServiceContract;
use App\Contracts\UserServiceClientContract;
use App\DTOs\AuthResultDto;
use App\DTOs\TokenPairDto;
use App\DTOs\UserInfoDto;
use App\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Redis;

/**
 * Local (username + password) identity provider.
 *
 * Implements the full IdentityProviderContract for credential-based local
 * authentication. All user lookups and credential validation are delegated
 * to the User microservice via UserServiceClientContract, maintaining strict
 * service isolation and no direct database access between services.
 *
 * Flow:
 *  1. authenticate()  — validate email + password via UserService, issue JWT + refresh token.
 *  2. getUserInfo()   — decode a previously issued JWT and enrich with User service data.
 *  3. logout()        — revoke the access token's JTI via TokenService.
 *  4. refreshToken()  — rotate the Redis-backed refresh token and issue a new JWT pair.
 *
 * OAuth2-specific methods (exchangeToken) are not applicable to local auth and
 * throw an AuthenticationException to guard against misuse.
 */
class LocalIdentityProvider implements IdentityProviderContract
{
    public function __construct(
        private readonly UserServiceClientContract $userServiceClient,
        private readonly TokenServiceContract      $tokenService,
        private readonly array                     $config = [],
    ) {}

    /**
     * Authenticate a user with email and password.
     *
     * Delegates user lookup and credential validation to the User service,
     * then issues a signed JWT access token and a rotating refresh token.
     *
     * Expected $credentials keys:
     *   - email       (required)
     *   - password    (required)
     *   - device_id   (optional, defaults to 'default')
     *   - tenant_id   (optional, resolved from user record if absent)
     *   - ip_address  (optional, forwarded to login-event audit log)
     *
     * @throws AuthenticationException on missing fields, unknown user, inactive account, or wrong password
     */
    public function authenticate(array $credentials): AuthResultDto
    {
        $email     = $credentials['email'] ?? '';
        $password  = $credentials['password'] ?? '';
        $deviceId  = $credentials['device_id'] ?? 'default';
        $tenantId  = $credentials['tenant_id'] ?? '';
        $ipAddress = $credentials['ip_address'] ?? '';

        if (empty($email) || empty($password)) {
            throw new AuthenticationException('Email and password are required');
        }

        $user = $this->userServiceClient->findByEmail($email);

        if (! $user) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (! $user->isActive()) {
            throw new AuthenticationException('Account is not active');
        }

        if (! $this->userServiceClient->validateCredentials($user->id, $password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $ttl     = $this->accessTtl();
        $claims  = $this->tokenService->buildClaims($user->toArray(), $deviceId, $tenantId ?: $user->tenantId);
        $access  = $this->tokenService->issue($claims, $ttl);
        $refresh = $this->tokenService->issueRefreshToken($user->id, $deviceId, $claims['jti']);

        if ($ipAddress) {
            $this->userServiceClient->recordLoginEvent($user->id, $deviceId, $ipAddress);
        }

        return new AuthResultDto(
            accessToken:  $access,
            refreshToken: $refresh,
            expiresIn:    $ttl,
            claims:       $claims,
        );
    }

    /**
     * Not applicable for local authentication.
     *
     * @throws AuthenticationException always
     */
    public function exchangeToken(string $code, string $redirectUri): TokenPairDto
    {
        throw new AuthenticationException('Local provider does not support OAuth2 token exchange');
    }

    /**
     * Extract user identity from a locally issued JWT access token.
     *
     * Decodes the token without signature verification (to allow expired tokens
     * during logout/refresh flows) and enriches the result with live user data
     * fetched from the User service.
     *
     * @throws AuthenticationException when the token cannot be decoded
     */
    public function getUserInfo(string $accessToken): UserInfoDto
    {
        try {
            $claims = $this->tokenService->decode($accessToken, false);
        } catch (\Throwable $e) {
            throw new AuthenticationException('Failed to decode access token: ' . $e->getMessage());
        }

        $userId = $claims['sub'] ?? '';
        $user   = $userId ? $this->userServiceClient->findById($userId) : null;

        return new UserInfoDto(
            externalId: $userId,
            email:      $user?->email ?? '',
            name:       $user?->name ?? '',
            provider:   'local',
            attributes: $claims,
        );
    }

    /**
     * Revoke the access token's JTI so it is immediately rejected by all services.
     *
     * Non-fatal: silently absorbs any decode or revocation errors so that the
     * caller's logout flow is never interrupted by a stale or invalid token.
     */
    public function logout(string $accessToken): void
    {
        try {
            $claims = $this->tokenService->decode($accessToken, false);
            $jti    = $claims['jti'] ?? '';

            if ($jti) {
                $this->tokenService->revoke($jti);
            }
        } catch (\Throwable) {
            // Non-fatal: token may already be expired or malformed
        }
    }

    /**
     * Rotate the local refresh token and issue a new JWT access + refresh pair.
     *
     * Validates the refresh token stored in Redis, invalidates it immediately
     * (rotation), and issues a new pair bound to the same device.
     *
     * @throws AuthenticationException when the token is invalid/expired or the user is inactive
     */
    public function refreshToken(string $refreshToken): TokenPairDto
    {
        $data = Redis::get("refresh:{$refreshToken}");

        if (! $data) {
            throw new AuthenticationException('Invalid or expired refresh token');
        }

        $stored   = (array) json_decode((string) $data, true);
        $userId   = $stored['user_id'] ?? '';
        $deviceId = $stored['device_id'] ?? '';

        $user = $this->userServiceClient->findById($userId);

        if (! $user || ! $user->isActive()) {
            throw new AuthenticationException('User not found or inactive');
        }

        // Rotate: invalidate the old refresh token immediately
        Redis::del("refresh:{$refreshToken}");

        $ttl        = $this->accessTtl();
        $claims     = $this->tokenService->buildClaims($user->toArray(), $deviceId, $user->tenantId);
        $access     = $this->tokenService->issue($claims, $ttl);
        $newRefresh = $this->tokenService->issueRefreshToken($userId, $deviceId, $claims['jti']);

        return new TokenPairDto(
            accessToken:  $access,
            refreshToken: $newRefresh,
            expiresIn:    $ttl,
        );
    }

    public function getProviderName(): string
    {
        return 'local';
    }

    public function supportsSSO(): bool
    {
        return false;
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    /** Return the configured JWT access-token TTL in seconds. */
    private function accessTtl(): int
    {
        return (int) config('jwt.ttl', 900);
    }
}
