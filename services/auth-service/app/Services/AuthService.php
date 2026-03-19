<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\SessionRepositoryInterface;
use App\Contracts\Repositories\TokenRevocationRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuditServiceInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\SessionServiceInterface;
use App\Contracts\Services\TenantConfigServiceInterface;
use App\Contracts\Services\TokenServiceInterface;
use App\DTOs\AuthResultDto;
use App\DTOs\LoginCredentialsDto;
use App\DTOs\LogoutContextDto;
use App\DTOs\TokenClaimsDto;
use App\DTOs\TokenPairDto;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Events\TokenRefreshed;
use App\Exceptions\AuthException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Ramsey\Uuid\Uuid;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TokenServiceInterface $tokenService,
        private readonly SessionServiceInterface $sessionService,
        private readonly AuditServiceInterface $auditService,
        private readonly PermissionServiceInterface $permissionService,
        private readonly TenantConfigServiceInterface $tenantConfigService,
    ) {}

    public function login(LoginCredentialsDto $credentials): AuthResultDto
    {
        // 1. Check suspicious activity before processing
        if ($this->auditService->isSuspiciousActivity('', $credentials->ipAddress)) {
            $this->auditService->logFailedLogin(
                $credentials->email,
                $credentials->tenantId,
                $credentials->ipAddress,
                $credentials->userAgent,
                'IP rate-limited due to suspicious activity',
            );
            throw new AuthException('Too many authentication attempts. Please try again later.', 429);
        }

        // 2. Find the user within the correct tenant scope
        $user = $this->userRepository->findByEmail($credentials->email, $credentials->tenantId);

        if ($user === null) {
            $this->auditService->logFailedLogin(
                $credentials->email,
                $credentials->tenantId,
                $credentials->ipAddress,
                $credentials->userAgent,
                'User not found',
            );
            throw new AuthException('Invalid credentials.', 401);
        }

        // 3. Check account status
        if (! $user->is_active) {
            throw new AuthException('Account is inactive.', 403);
        }

        if ($user->isLocked()) {
            throw new AuthException('Account is temporarily locked. Please try again later.', 423);
        }

        // 4. Verify password
        if (! Hash::check($credentials->password, $user->password)) {
            $this->userRepository->incrementFailedLoginAttempts($user->id);
            $this->auditService->logFailedLogin(
                $credentials->email,
                $credentials->tenantId,
                $credentials->ipAddress,
                $credentials->userAgent,
                'Invalid password',
            );

            $failedAttempts = $user->failed_login_attempts + 1;
            $maxAttempts = config('rate_limit.suspicious_activity.max_failed_logins', 5);

            if ($failedAttempts >= $maxAttempts) {
                $lockMinutes = config('rate_limit.suspicious_activity.lock_duration_minutes', 30);
                $this->userRepository->lockUser($user->id, $lockMinutes);
                $this->auditService->logSuspiciousActivity(
                    $user->id,
                    $credentials->tenantId,
                    'account_locked_due_to_failed_logins',
                    ['attempts' => $failedAttempts, 'ip' => $credentials->ipAddress],
                );
                throw new AuthException('Account has been temporarily locked due to too many failed attempts.', 423);
            }

            throw new AuthException('Invalid credentials.', 401);
        }

        // 5. Reset failed login attempts on success
        $this->userRepository->resetFailedLoginAttempts($user->id);
        $this->userRepository->updateLastLoginAt($user->id, $credentials->ipAddress);

        // 6. Enforce device limit per tenant config
        $maxDevices = $this->tenantConfigService->getMaxDevicesPerUser($credentials->tenantId);
        $sessionService = $this->sessionService;

        // 7. Build token claims
        $roles = $this->permissionService->getUserRoles($user->id, $credentials->tenantId);
        $permissions = $this->permissionService->getUserPermissions($user->id, $credentials->tenantId);

        $accessTtl = $this->tenantConfigService->getAccessTokenTtl($credentials->tenantId);
        $refreshTtl = $this->tenantConfigService->getRefreshTokenTtl($credentials->tenantId);

        $jti = Uuid::uuid4()->toString();

        $claims = new TokenClaimsDto(
            userId: $user->id,
            tenantId: $credentials->tenantId,
            organisationId: $credentials->organisationId ?? $user->organisation_id,
            branchId: $credentials->branchId ?? $user->branch_id,
            locationId: $user->location_id,
            departmentId: $user->department_id,
            roles: $roles,
            permissions: $permissions,
            deviceId: $credentials->deviceId,
            tokenVersion: $user->token_version,
            ttlMinutes: $accessTtl,
            jti: $jti,
        );

        // 8. Issue tokens
        $accessToken = $this->tokenService->issueAccessToken($claims);
        $rawRefreshToken = $this->tokenService->issueRefreshToken($user->id, '');
        $hashedRefreshToken = $this->tokenService->hashRefreshToken($rawRefreshToken);

        $refreshExpiresAt = now()->addMinutes($refreshTtl);

        // 9. Create session
        $session = $sessionService->createSession(
            userId: $user->id,
            tenantId: $credentials->tenantId,
            deviceId: $credentials->deviceId,
            deviceName: $credentials->deviceName,
            ipAddress: $credentials->ipAddress,
            userAgent: $credentials->userAgent,
            hashedRefreshToken: $hashedRefreshToken,
            refreshTokenExpiresAt: $refreshExpiresAt,
        );

        // 10. Audit event
        $this->auditService->log(
            event: 'user.login',
            userId: $user->id,
            tenantId: $credentials->tenantId,
            metadata: ['device_id' => $credentials->deviceId, 'session_id' => $session->id],
            ipAddress: $credentials->ipAddress,
            userAgent: $credentials->userAgent,
        );

        event(new UserLoggedIn($user, $credentials->tenantId, $session->id, $credentials->ipAddress));

        return new AuthResultDto(
            user: $user,
            tokenPair: new TokenPairDto(
                accessToken: $accessToken,
                refreshToken: $rawRefreshToken,
                accessTokenExpiresIn: $accessTtl * 60,
                refreshTokenExpiresIn: $refreshTtl * 60,
            ),
            sessionId: $session->id,
        );
    }

    public function logout(LogoutContextDto $context): void
    {
        // 1. Revoke the access token JTI
        $this->tokenService->revokeByJti(
            $context->accessTokenJti,
            $context->userId,
            $context->accessTokenRemainingTtlSeconds,
            'logout',
        );

        // 2. Revoke the device session (which invalidates the refresh token)
        $this->sessionService->revokeSession($context->sessionId, $context->userId);

        // 3. Audit
        $this->auditService->log(
            event: 'user.logout',
            userId: $context->userId,
            tenantId: $context->tenantId,
            metadata: ['session_id' => $context->sessionId, 'device_id' => $context->deviceId],
            ipAddress: $context->ipAddress,
        );

        event(new UserLoggedOut($context->userId, $context->tenantId, 'single_device'));
    }

    public function logoutAllDevices(string $userId, string $tenantId): void
    {
        // Revoke all sessions — the token version increment handles existing access tokens
        $this->sessionService->revokeAllSessions($userId);
        $this->userRepository->incrementTokenVersion($userId);
        $this->permissionService->invalidateCache($userId, $tenantId);

        $this->auditService->log(
            event: 'user.logout_all',
            userId: $userId,
            tenantId: $tenantId,
            metadata: ['reason' => 'global_logout'],
        );

        event(new UserLoggedOut($userId, $tenantId, 'all_devices'));
    }

    public function logoutDevice(string $userId, string $deviceId, string $tenantId): void
    {
        $this->sessionService->revokeDeviceSession($userId, $deviceId);

        $this->auditService->log(
            event: 'user.logout_device',
            userId: $userId,
            tenantId: $tenantId,
            metadata: ['device_id' => $deviceId],
        );
    }

    public function refreshTokens(string $refreshToken, string $deviceId): TokenPairDto
    {
        // 1. Find session by refresh token
        $session = $this->sessionService->findByRefreshToken($refreshToken);

        if ($session === null || ! $session->is_active || $session->isExpired()) {
            throw new AuthException('Invalid or expired refresh token.', 401);
        }

        if ($session->device_id !== $deviceId) {
            // Token theft detection: device mismatch
            $this->auditService->logSuspiciousActivity(
                $session->user_id,
                $session->tenant_id,
                'refresh_token_device_mismatch',
                ['expected_device' => $session->device_id, 'provided_device' => $deviceId],
            );
            $this->sessionService->revokeAllSessions($session->user_id);
            $this->userRepository->incrementTokenVersion($session->user_id);
            throw new AuthException('Security violation detected. All sessions revoked.', 401);
        }

        // 2. Verify the refresh token hash
        if (! $this->tokenService->verifyRefreshToken($refreshToken, $session->refresh_token_hash)) {
            throw new AuthException('Invalid refresh token.', 401);
        }

        // 3. Load the user
        $user = $this->userRepository->findById($session->user_id);

        if ($user === null || ! $user->is_active || $user->isLocked()) {
            throw new AuthException('User account is not accessible.', 403);
        }

        // 4. Issue new token pair
        $roles = $this->permissionService->getUserRoles($user->id, $session->tenant_id);
        $permissions = $this->permissionService->getUserPermissions($user->id, $session->tenant_id);

        $accessTtl = $this->tenantConfigService->getAccessTokenTtl($session->tenant_id);
        $refreshTtl = $this->tenantConfigService->getRefreshTokenTtl($session->tenant_id);

        $claims = new TokenClaimsDto(
            userId: $user->id,
            tenantId: $session->tenant_id,
            organisationId: $user->organisation_id,
            branchId: $user->branch_id,
            locationId: $user->location_id,
            departmentId: $user->department_id,
            roles: $roles,
            permissions: $permissions,
            deviceId: $deviceId,
            tokenVersion: $user->token_version,
            ttlMinutes: $accessTtl,
            jti: Uuid::uuid4()->toString(),
        );

        $newAccessToken = $this->tokenService->issueAccessToken($claims);
        $newRawRefreshToken = $this->tokenService->issueRefreshToken($user->id, $session->id);
        $newHashedRefreshToken = $this->tokenService->hashRefreshToken($newRawRefreshToken);
        $newRefreshExpiresAt = now()->addMinutes($refreshTtl);

        // 5. Rotate refresh token in session
        $this->sessionService->rotateRefreshToken(
            $session->id,
            $newHashedRefreshToken,
            $newRefreshExpiresAt,
        );

        $this->auditService->log(
            event: 'token.refreshed',
            userId: $user->id,
            tenantId: $session->tenant_id,
            metadata: ['session_id' => $session->id],
        );

        event(new TokenRefreshed($user->id, $session->tenant_id, $session->id));

        return new TokenPairDto(
            accessToken: $newAccessToken,
            refreshToken: $newRawRefreshToken,
            accessTokenExpiresIn: $accessTtl * 60,
            refreshTokenExpiresIn: $refreshTtl * 60,
        );
    }

    public function validateAccessToken(string $accessToken): array
    {
        return $this->tokenService->decodeAccessToken($accessToken);
    }

    public function register(array $userData, string $tenantId): AuthResultDto
    {
        if ($this->userRepository->existsByEmail($userData['email'], $tenantId)) {
            throw new AuthException('Email address is already registered.', 409);
        }

        $user = $this->userRepository->create([
            'tenant_id'           => $tenantId,
            'name'                => $userData['name'],
            'email'               => $userData['email'],
            'password'            => $userData['password'],
            'organisation_id'     => $userData['organisation_id'] ?? null,
            'branch_id'           => $userData['branch_id'] ?? null,
            'password_changed_at' => now(),
        ]);

        $this->auditService->log(
            event: 'user.registered',
            userId: $user->id,
            tenantId: $tenantId,
            metadata: ['email' => $userData['email']],
        );

        $credentials = LoginCredentialsDto::fromArray([
            'email'       => $userData['email'],
            'password'    => $userData['password'],
            'tenant_id'   => $tenantId,
            'device_id'   => $userData['device_id'] ?? Uuid::uuid4()->toString(),
            'device_name' => $userData['device_name'] ?? 'Registration Device',
            'ip_address'  => $userData['ip_address'] ?? '',
            'user_agent'  => $userData['user_agent'] ?? '',
        ]);

        return $this->login($credentials);
    }

    public function changePassword(string $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new AuthException('User not found.', 404);
        }

        if (! Hash::check($currentPassword, $user->password)) {
            throw new AuthException('Current password is incorrect.', 422);
        }

        $this->userRepository->updatePassword($userId, Hash::make($newPassword));

        if (config('jwt.version_on_password_change', true)) {
            $this->userRepository->incrementTokenVersion($userId);
            $this->sessionService->revokeAllSessions($userId);
        }

        $this->auditService->log(
            event: 'user.password_changed',
            userId: $userId,
            tenantId: $user->tenant_id,
        );
    }

    public function initiatePasswordReset(string $email, string $tenantId): void
    {
        $user = $this->userRepository->findByEmail($email, $tenantId);

        if ($user === null) {
            // Silently succeed to prevent user enumeration
            return;
        }

        // Use Laravel's built-in password reset mechanism
        Password::sendResetLink(['email' => $email]);

        $this->auditService->log(
            event: 'user.password_reset_initiated',
            userId: $user->id,
            tenantId: $tenantId,
            metadata: ['email' => $email],
        );
    }

    public function completePasswordReset(string $resetToken, string $newPassword): void
    {
        $status = Password::reset(
            ['token' => $resetToken, 'password' => $newPassword, 'password_confirmation' => $newPassword],
            function (User $user, string $password) {
                $this->userRepository->updatePassword($user->id, Hash::make($password));
                $this->userRepository->incrementTokenVersion($user->id);
                $this->sessionService->revokeAllSessions($user->id);

                $this->auditService->log(
                    event: 'user.password_reset_completed',
                    userId: $user->id,
                    tenantId: $user->tenant_id,
                );
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new AuthException('Invalid or expired password reset token.', 422);
        }
    }
}
