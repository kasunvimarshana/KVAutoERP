<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\RefreshTokenRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuditLogServiceInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\RevocationServiceInterface;
use App\Contracts\Services\UserProviderInterface;
use App\Exceptions\AccountInactiveException;
use App\Exceptions\AuthenticationException;
use App\Exceptions\InvalidRefreshTokenException;
use App\IdentityProviders\IdentityProviderFactory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use KvEnterprise\SharedKernel\Contracts\Auth\TokenServiceInterface;
use Ramsey\Uuid\Uuid;

/**
 * Core authentication orchestration service.
 *
 * Coordinates credential verification, token issuance, refresh-token
 * rotation, session revocation, and audit logging.
 */
final class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface         $userRepository,
        private readonly RefreshTokenRepositoryInterface $refreshTokenRepository,
        private readonly TokenServiceInterface           $tokenService,
        private readonly RevocationServiceInterface      $revocationService,
        private readonly AuditLogServiceInterface        $auditLogService,
        private readonly IdentityProviderFactory         $identityProviderFactory,
        private readonly ?UserProviderInterface          $userProvider = null,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function login(
        string $email,
        string $password,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
    ): array {
        // Resolve the IAM provider for this tenant (local, OAuth2, Keycloak, etc.).
        $identityProvider = $this->identityProviderFactory->resolveForTenant($tenantId);
        $identity         = $identityProvider->authenticate($email, $password, $tenantId);

        if ($identity === null) {
            // Do not query the user table here — doing so on every failed attempt
            // would expose a timing oracle and enable credential-stuffing amplification.
            // The audit log accepts a null userId for unauthenticated failure events.
            $this->auditLogService->logFailedLogin(
                userId: null,
                tenantId: $tenantId,
                email: $email,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: ['provider' => $identityProvider->getProviderName()],
            );

            $this->detectSuspiciousActivity($email, $tenantId, $ipAddress, $userAgent);

            throw new AuthenticationException();
        }

        // Load the auth-service User model for session/refresh-token management.
        $user = $this->userRepository->findById((string) ($identity['user_id'] ?? ''));

        if ($user === null) {
            // For external IAM providers the user may not have a local record yet.
            // Emit a warning and throw — the User Service should have provisioned one.
            Log::warning('AuthService: no local user record after successful IAM auth', [
                'email'     => $email,
                'tenant_id' => $tenantId,
                'provider'  => $identityProvider->getProviderName(),
            ]);

            throw new AuthenticationException();
        }

        if (!$user->isActive()) {
            $this->auditLogService->logFailedLogin(
                userId: $user->id,
                tenantId: $tenantId,
                email: $email,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: ['reason' => 'account_inactive'],
            );

            throw new AccountInactiveException();
        }

        $tokenPair = $this->issueTokenPair($user, $deviceId);

        $this->auditLogService->logLogin(
            userId: $user->id,
            tenantId: $tenantId,
            deviceId: $deviceId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: ['provider' => $identityProvider->getProviderName()],
        );

        return $tokenPair;
    }

    /**
     * {@inheritDoc}
     */
    public function logout(string $accessToken, string $ipAddress, string $userAgent): bool
    {
        $claims = $this->tokenService->decode($accessToken);

        $this->tokenService->revoke($accessToken);

        $userId   = $claims['user_id'] ?? null;
        $tenantId = $claims['tenant_id'] ?? null;
        $deviceId = $claims['device_id'] ?? null;
        $jti      = $claims['jti'] ?? null;

        // Revoke the associated refresh token for this device.
        if ($userId !== null && $deviceId !== null) {
            $this->refreshTokenRepository->revokeForDevice((string) $userId, (string) $deviceId);
        }

        if ($userId !== null && $tenantId !== null && $deviceId !== null) {
            $this->auditLogService->logLogout(
                userId: (string) $userId,
                tenantId: (string) $tenantId,
                deviceId: (string) $deviceId,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: ['jti' => $jti],
            );
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshTokens(
        string $refreshToken,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
    ): array {
        $tokenHash    = hash('sha256', $refreshToken);
        $storedToken  = $this->refreshTokenRepository->findValidByHash($tokenHash);

        if ($storedToken === null || !$storedToken->isValid()) {
            throw new InvalidRefreshTokenException();
        }

        $user = $this->userRepository->findById($storedToken->user_id);

        if ($user === null || !$user->isActive()) {
            throw new InvalidRefreshTokenException('User account is inactive or not found.');
        }

        // Revoke the used refresh token (rotation).
        $this->refreshTokenRepository->revoke($storedToken->id);

        // Issue a fresh token pair.
        $tokenPair = $this->issueTokenPair($user, $storedToken->device_id);

        $this->auditLogService->logTokenRefresh(
            userId: $user->id,
            tenantId: $user->tenant_id,
            deviceId: $storedToken->device_id,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return $tokenPair;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllSessions(string $userId, string $ipAddress, string $userAgent): bool
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return false;
        }

        // Increment token_version in both the DB and Redis.
        $newVersion = $this->userRepository->incrementTokenVersion($userId);
        $this->revocationService->revokeAllForUser($userId);
        $this->refreshTokenRepository->revokeAllForUser($userId);

        $this->auditLogService->logGlobalRevocation(
            userId: $userId,
            tenantId: $user->tenant_id,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: ['new_token_version' => $newVersion],
        );

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeDeviceSession(
        string $userId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
    ): bool {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return false;
        }

        $this->revocationService->revokeForDevice($userId, $deviceId);
        $this->refreshTokenRepository->revokeForDevice($userId, $deviceId);

        $this->auditLogService->logDeviceRevocation(
            userId: $userId,
            tenantId: $user->tenant_id,
            deviceId: $deviceId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function registerUser(array $data, string $tenantId, string $actorId): User
    {
        $data['tenant_id'] = $tenantId;
        $data['password']  = $this->hashPassword((string) $data['password']);
        $data['roles']       = $data['roles'] ?? [];
        $data['permissions'] = $data['permissions'] ?? [];

        $user = $this->userRepository->create($data);

        $this->auditLogService->logUserRegistration(
            newUserId: $user->id,
            tenantId: $tenantId,
            actorId: $actorId,
            ipAddress: request()->ip() ?? '0.0.0.0',
            userAgent: request()->userAgent() ?? '',
        );

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserFromClaims(array $claims): ?User
    {
        $userId = $claims['user_id'] ?? null;

        if ($userId === null) {
            return null;
        }

        return $this->userRepository->findById((string) $userId);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build the full JWT claims array for a user + device and issue tokens.
     *
     * When the User Service client is configured, it calls the User Service's
     * internal claims endpoint to retrieve enriched roles, permissions, and
     * tenant-hierarchy data. Falls back to the auth user's own fields if the
     * User Service is unavailable (fail-open for resilience).
     *
     * @param  User    $user
     * @param  string  $deviceId
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     */
    private function issueTokenPair(User $user, string $deviceId): array
    {
        $currentVersion = $this->revocationService->getUserTokenVersion($user->id);

        // Attempt to enrich claims from the User Service.
        $enrichedClaims = $this->userProvider?->getClaimsForUser($user->id, $user->tenant_id);

        $claims = [
            'user_id'         => $user->id,
            'tenant_id'       => $user->tenant_id,
            // organization_id and branch_id exist on both the auth User model and
            // User Service profile — fall back to the auth model when User Service
            // is unavailable.
            'organization_id' => $enrichedClaims['organization_id'] ?? $user->organization_id,
            'branch_id'       => $enrichedClaims['branch_id']       ?? $user->branch_id,
            // location_id and department_id are only stored in the User Service
            // (UserProfile) — not on the auth User model. Intentionally null when
            // User Service is unavailable; callers must tolerate null here.
            'location_id'     => $enrichedClaims['location_id']     ?? null,
            'department_id'   => $enrichedClaims['department_id']   ?? null,
            'roles'           => $enrichedClaims['roles']           ?? $user->roles ?? [],
            'permissions'     => $enrichedClaims['permissions']     ?? $user->permissions ?? [],
            'device_id'       => $deviceId,
            'token_version'   => $currentVersion,
        ];

        $ttl         = (int) config('jwt.access_token_ttl', 900);
        $accessToken = $this->tokenService->issue($claims, $ttl);

        $rawRefreshToken   = Uuid::uuid4()->toString() . '.' . base64_encode(random_bytes(32));
        $refreshTokenHash  = hash('sha256', $rawRefreshToken);
        $refreshTtl        = (int) config('jwt.refresh_token_ttl', 604800);

        $this->refreshTokenRepository->create([
            'user_id'     => $user->id,
            'tenant_id'   => $user->tenant_id,
            'device_id'   => $deviceId,
            'token_hash'  => $refreshTokenHash,
            'expires_at'  => now()->addSeconds($refreshTtl),
        ]);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefreshToken,
            'expires_in'    => $ttl,
            'token_type'    => 'Bearer',
        ];
    }

    /**
     * Hash a plain-text password using Argon2id.
     *
     * @param  string  $password
     * @return string
     */
    private function hashPassword(string $password): string
    {
        return password_hash(
            $password,
            (int) config('auth_service.password_algo', PASSWORD_ARGON2ID),
            [
                'memory_cost' => (int) config('auth_service.argon2.memory', 65536),
                'time_cost'   => (int) config('auth_service.argon2.time', 4),
                'threads'     => (int) config('auth_service.argon2.threads', 1),
            ],
        );
    }

    /**
     * Detect and log suspicious login activity based on recent failure count.
     *
     * @param  string  $email
     * @param  string  $tenantId
     * @param  string  $ipAddress
     * @param  string  $userAgent
     * @return void
     */
    private function detectSuspiciousActivity(
        string $email,
        string $tenantId,
        string $ipAddress,
        string $userAgent,
    ): void {
        $threshold = (int) config('auth_service.suspicious.failed_attempts_threshold', 5);
        $cacheKey  = "auth:failed_attempts:{$ipAddress}:{$tenantId}";

        $attempts = (int) cache()->get($cacheKey, 0) + 1;
        cache()->put($cacheKey, $attempts, now()->addMinutes(
            (int) config('auth_service.suspicious.lockout_minutes', 15),
        ));

        if ($attempts >= $threshold) {
            $this->auditLogService->logSuspiciousActivity(
                userId: null,
                tenantId: $tenantId,
                eventType: 'excessive_failed_logins',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: [
                    'email'    => $email,
                    'attempts' => $attempts,
                ],
            );
        }
    }
}
