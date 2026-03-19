<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AuthServiceContract;
use App\Contracts\RevocationServiceContract;
use App\Contracts\SuspiciousActivityServiceContract;
use App\Contracts\TokenServiceContract;
use App\Contracts\UserServiceClientContract;
use App\DTOs\AuthResultDto;
use App\DTOs\TokenClaimsDto;
use App\DTOs\TokenPairDto;
use App\Events\TokenRefreshed;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Exceptions\AuthenticationException;
use App\Exceptions\TokenException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AuthService implements AuthServiceContract
{
    public function __construct(
        private readonly TokenServiceContract              $tokenService,
        private readonly RevocationServiceContract         $revocationService,
        private readonly UserServiceClientContract         $userServiceClient,
        private readonly IdentityProviderManager           $identityProviderManager,
        private readonly SuspiciousActivityServiceContract $suspiciousActivityService,
    ) {}

    public function login(array $credentials, string $deviceId, string $ipAddress): AuthResultDto
    {
        $provider = $credentials['provider'] ?? 'local';
        $tenantId = $credentials['tenant_id'] ?? '';
        $email    = $credentials['email'] ?? '';

        // ── Suspicious activity guard ──────────────────────────────────
        if ($this->suspiciousActivityService->isBlocked($email) || $this->suspiciousActivityService->isBlocked($ipAddress)) {
            throw new AuthenticationException('Too many failed attempts. Please try again later.');
        }

        try {
            // ── Resolve tenant IAM provider dynamically from user-service ──
            if ($tenantId && $provider === 'local') {
                $tenantConfig   = $this->userServiceClient->getTenantIamConfig($tenantId);
                $tenantProvider = $tenantConfig['iam_provider'] ?? 'local';

                // If the tenant is configured for a federated provider, redirect the login
                if ($tenantProvider !== 'local') {
                    $provider    = $tenantProvider;
                    $iamConfig   = (array) ($tenantConfig['iam_config'] ?? []);

                    // Dynamically register tenant-specific IAM config for this request
                    $this->identityProviderManager->registerTenantConfig($tenantProvider, $tenantId, $iamConfig);
                }
            }

            // Federated / SSO login via IAM provider adapter
            if ($provider !== 'local') {
                $idp       = $this->identityProviderManager->resolve($provider, $tenantId);
                $userInfo  = null;
                $tokenPair = null;

                if (! empty($credentials['code'])) {
                    // OAuth2 authorization-code flow
                    $tokenPair = $idp->exchangeToken(
                        $credentials['code'],
                        $credentials['redirect_uri'] ?? ''
                    );
                    $userInfo  = $idp->getUserInfo($tokenPair->accessToken);
                } else {
                    // Resource-owner password flow (Okta, Keycloak)
                    $authResult = $idp->authenticate($credentials);

                    return $authResult;
                }

                // Map external identity → internal user
                $user = $this->userServiceClient->findByExternalId($userInfo->externalId, $provider)
                    ?? $this->userServiceClient->findByEmail($userInfo->email);

                if (! $user) {
                    $this->suspiciousActivityService->recordFailedAttempt($email, $ipAddress);
                    throw new AuthenticationException('User not found for federated identity');
                }

                if (! $user->isActive()) {
                    throw new AuthenticationException('Account is not active');
                }

                $ttl     = (int) config('jwt.ttl', 900);
                $claims  = $this->tokenService->buildClaims($user->toArray(), $deviceId, $tenantId ?: $user->tenantId);
                $access  = $this->tokenService->issue($claims, $ttl);
                $refresh = $this->tokenService->issueRefreshToken($user->id, $deviceId, $claims['jti']);

                $this->suspiciousActivityService->resetFailedAttempts($email);
                $this->userServiceClient->recordLoginEvent($user->id, $deviceId, $ipAddress);
                event(new UserLoggedIn($user->id, $deviceId, $ipAddress, $user->tenantId));

                return new AuthResultDto(
                    accessToken:  $access,
                    refreshToken: $refresh,
                    expiresIn:    $ttl,
                    claims:       $claims,
                );
            }

            // Local authentication
            $user = $this->userServiceClient->findByEmail($email);

            if (! $user) {
                $this->suspiciousActivityService->recordFailedAttempt($email, $ipAddress);
                throw new AuthenticationException('Invalid credentials');
            }

            if (! $user->isActive()) {
                throw new AuthenticationException('Account is not active');
            }

            if (! $this->userServiceClient->validateCredentials($user->id, $credentials['password'] ?? '')) {
                $blocked = $this->suspiciousActivityService->recordFailedAttempt($email, $ipAddress);

                $remaining = $this->suspiciousActivityService->remainingAttempts($email);

                Log::warning('Failed login attempt', [
                    'email'     => $email,
                    'ip'        => $ipAddress,
                    'remaining' => $remaining,
                    'blocked'   => $blocked,
                ]);

                throw new AuthenticationException('Invalid credentials');
            }

            $ttl     = (int) config('jwt.ttl', 900);
            $claims  = $this->tokenService->buildClaims($user->toArray(), $deviceId, $tenantId ?: $user->tenantId);
            $access  = $this->tokenService->issue($claims, $ttl);
            $refresh = $this->tokenService->issueRefreshToken($user->id, $deviceId, $claims['jti']);

            $this->suspiciousActivityService->resetFailedAttempts($email);
            $this->userServiceClient->recordLoginEvent($user->id, $deviceId, $ipAddress);
            event(new UserLoggedIn($user->id, $deviceId, $ipAddress, $user->tenantId));

            return new AuthResultDto(
                accessToken:  $access,
                refreshToken: $refresh,
                expiresIn:    $ttl,
                claims:       $claims,
            );

        } catch (AuthenticationException $e) {
            Log::warning('Login failed', [
                'email'  => $email,
                'ip'     => $ipAddress,
                'reason' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Unexpected login error', ['error' => $e->getMessage()]);
            throw new AuthenticationException('Authentication failed');
        }
    }

    public function logout(string $accessToken, ?string $deviceId = null, bool $allDevices = false): void
    {
        try {
            $claims = $this->tokenService->decode($accessToken, false);
            $jti    = $claims['jti'] ?? '';
            $userId = $claims['sub'] ?? '';

            if ($jti) {
                $this->tokenService->revoke($jti);
            }

            if ($allDevices) {
                $this->revocationService->revokeAll($userId);
            } elseif ($deviceId) {
                $this->revocationService->revokeDevice($userId, $deviceId);
            }

            event(new UserLoggedOut($userId, $deviceId, $allDevices));
        } catch (\Throwable $e) {
            Log::warning('Logout error (non-fatal)', ['error' => $e->getMessage()]);
        }
    }

    public function refreshToken(string $refreshToken, string $deviceId): TokenPairDto
    {
        $key  = "refresh:{$refreshToken}";
        $data = Redis::get($key);

        if (! $data) {
            throw new TokenException('Invalid or expired refresh token');
        }

        $stored = (array) json_decode((string) $data, true);

        if (($stored['device_id'] ?? '') !== $deviceId) {
            throw new TokenException('Refresh token device mismatch');
        }

        $userId = $stored['user_id'] ?? '';
        $user   = $this->userServiceClient->findById($userId);

        if (! $user || ! $user->isActive()) {
            throw new TokenException('User not found or inactive');
        }

        // Rotate: invalidate old refresh token
        Redis::del($key);

        $ttl     = (int) config('jwt.ttl', 900);
        $claims  = $this->tokenService->buildClaims($user->toArray(), $deviceId, $user->tenantId);
        $access  = $this->tokenService->issue($claims, $ttl);
        $refresh = $this->tokenService->issueRefreshToken($userId, $deviceId, $claims['jti']);

        event(new TokenRefreshed($userId, $deviceId));

        return new TokenPairDto(
            accessToken:  $access,
            refreshToken: $refresh,
            expiresIn:    $ttl,
        );
    }

    public function revokeToken(string $jti): void
    {
        $this->tokenService->revoke($jti);
    }

    public function revokeAllUserTokens(string $userId): void
    {
        $this->revocationService->revokeAll($userId);
    }

    public function verifyToken(string $accessToken): TokenClaimsDto
    {
        $c = $this->tokenService->verify($accessToken);

        return new TokenClaimsDto(
            jti:            $c['jti'] ?? '',
            userId:         $c['sub'] ?? '',
            tenantId:       $c['tenant_id'] ?? '',
            organizationId: $c['org_id'] ?? '',
            branchId:       $c['branch_id'] ?? '',
            roles:          (array) ($c['roles'] ?? []),
            permissions:    (array) ($c['permissions'] ?? []),
            deviceId:       $c['device_id'] ?? '',
            tokenVersion:   (int) ($c['token_version'] ?? 1),
            provider:       $c['provider'] ?? 'local',
            issuer:         $c['iss'] ?? '',
            exp:            (int) ($c['exp'] ?? 0),
            iat:            (int) ($c['iat'] ?? 0),
        );
    }

    public function issueServiceToken(string $serviceId, string $serviceSecret): TokenPairDto
    {
        $services = (array) config('auth.service_credentials', []);

        if (! isset($services[$serviceId])) {
            throw new AuthenticationException('Invalid service credentials');
        }

        if (! hash_equals($services[$serviceId], hash('sha256', $serviceSecret))) {
            throw new AuthenticationException('Invalid service credentials');
        }

        $ttl    = (int) config('jwt.service_ttl', 3600);
        $claims = [
            'sub'           => $serviceId,
            'type'          => 'service',
            'tenant_id'     => 'system',
            'org_id'        => '',
            'branch_id'     => '',
            'roles'         => ['service'],
            'permissions'   => [],
            'device_id'     => 'service',
            'token_version' => 1,
            'provider'      => 'service',
        ];

        $access  = $this->tokenService->issue($claims, $ttl);
        $refresh = $this->tokenService->issueRefreshToken($serviceId, 'service', $claims['jti'] ?? '');

        return new TokenPairDto(
            accessToken:  $access,
            refreshToken: $refresh,
            expiresIn:    $ttl,
        );
    }
}
