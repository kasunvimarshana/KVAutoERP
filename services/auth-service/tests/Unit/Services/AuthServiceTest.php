<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\SessionRepositoryInterface;
use App\Contracts\Repositories\TokenRevocationRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuditServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\SessionServiceInterface;
use App\Contracts\Services\TenantConfigServiceInterface;
use App\Contracts\Services\TokenServiceInterface;
use App\DTOs\LoginCredentialsDto;
use App\DTOs\TokenPairDto;
use App\Exceptions\AuthException;
use App\Models\DeviceSession;
use App\Models\User;
use App\Services\AuthService;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private MockInterface $userRepository;
    private MockInterface $tokenService;
    private MockInterface $sessionService;
    private MockInterface $auditService;
    private MockInterface $permissionService;
    private MockInterface $tenantConfigService;
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository      = Mockery::mock(UserRepositoryInterface::class);
        $this->tokenService        = Mockery::mock(TokenServiceInterface::class);
        $this->sessionService      = Mockery::mock(SessionServiceInterface::class);
        $this->auditService        = Mockery::mock(AuditServiceInterface::class);
        $this->permissionService   = Mockery::mock(PermissionServiceInterface::class);
        $this->tenantConfigService = Mockery::mock(TenantConfigServiceInterface::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->tokenService,
            $this->sessionService,
            $this->auditService,
            $this->permissionService,
            $this->tenantConfigService,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────
    // Login Tests
    // ─────────────────────────────────────────────────────────────────

    public function test_login_throws_auth_exception_when_user_not_found(): void
    {
        $credentials = $this->makeCredentials();

        $this->auditService->shouldReceive('isSuspiciousActivity')->once()->andReturn(false);
        $this->userRepository->shouldReceive('findByEmail')->once()->andReturn(null);
        $this->auditService->shouldReceive('logFailedLogin')->once();

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);

        $this->authService->login($credentials);
    }

    public function test_login_throws_auth_exception_when_account_is_inactive(): void
    {
        $credentials = $this->makeCredentials();
        $user = $this->makeUser(['is_active' => false]);

        $this->auditService->shouldReceive('isSuspiciousActivity')->once()->andReturn(false);
        $this->userRepository->shouldReceive('findByEmail')->once()->andReturn($user);

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(403);

        $this->authService->login($credentials);
    }

    public function test_login_throws_auth_exception_when_account_is_locked(): void
    {
        $credentials = $this->makeCredentials();
        $user = $this->makeUser([
            'is_active'    => true,
            'is_locked'    => true,
            'locked_until' => now()->addMinutes(30),
        ]);

        $this->auditService->shouldReceive('isSuspiciousActivity')->once()->andReturn(false);
        $this->userRepository->shouldReceive('findByEmail')->once()->andReturn($user);

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(423);

        $this->authService->login($credentials);
    }

    public function test_login_throws_auth_exception_on_invalid_password(): void
    {
        $credentials = $this->makeCredentials(['password' => 'wrong-password']);
        $user = $this->makeUser(['password' => Hash::make('correct-password')]);

        $this->auditService->shouldReceive('isSuspiciousActivity')->once()->andReturn(false);
        $this->userRepository->shouldReceive('findByEmail')->once()->andReturn($user);
        $this->userRepository->shouldReceive('incrementFailedLoginAttempts')->once();
        $this->auditService->shouldReceive('logFailedLogin')->once();

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);

        $this->authService->login($credentials);
    }

    public function test_login_locks_account_after_max_failed_attempts(): void
    {
        $credentials = $this->makeCredentials(['password' => 'wrong-password']);
        $user = $this->makeUser([
            'password'              => Hash::make('correct-password'),
            'failed_login_attempts' => 4, // one more attempt will trigger lock
        ]);

        $this->auditService->shouldReceive('isSuspiciousActivity')->once()->andReturn(false);
        $this->userRepository->shouldReceive('findByEmail')->once()->andReturn($user);
        $this->userRepository->shouldReceive('incrementFailedLoginAttempts')->once();
        $this->auditService->shouldReceive('logFailedLogin')->once();
        $this->userRepository->shouldReceive('lockUser')->once();
        $this->auditService->shouldReceive('logSuspiciousActivity')->once();

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(423);

        $this->authService->login($credentials);
    }

    public function test_login_throws_exception_on_suspicious_ip(): void
    {
        $credentials = $this->makeCredentials();

        $this->auditService->shouldReceive('isSuspiciousActivity')->once()->andReturn(true);
        $this->auditService->shouldReceive('logFailedLogin')->once();

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(429);

        $this->authService->login($credentials);
    }

    public function test_logout_all_revokes_all_sessions_and_increments_token_version(): void
    {
        $userId   = 'user-id-123';
        $tenantId = 'tenant-id-456';

        $this->sessionService->shouldReceive('revokeAllSessions')->once()->with($userId);
        $this->userRepository->shouldReceive('incrementTokenVersion')->once()->with($userId);
        $this->permissionService->shouldReceive('invalidateCache')->once()->with($userId, $tenantId);
        $this->auditService->shouldReceive('log')->once();

        $this->authService->logoutAllDevices($userId, $tenantId);
    }

    public function test_refresh_tokens_detects_device_mismatch_and_locks_all_sessions(): void
    {
        $rawRefreshToken = 'valid-refresh-token';
        $deviceId = 'device-1';

        $session = Mockery::mock(DeviceSession::class)->makePartial();
        $session->user_id    = 'user-123';
        $session->tenant_id  = 'tenant-456';
        $session->device_id  = 'device-2'; // Different device!
        $session->is_active  = true;

        $session->shouldReceive('isExpired')->andReturn(false);

        $this->sessionService->shouldReceive('findByRefreshToken')->once()->andReturn($session);
        $this->auditService->shouldReceive('logSuspiciousActivity')->once();
        $this->sessionService->shouldReceive('revokeAllSessions')->once();
        $this->userRepository->shouldReceive('incrementTokenVersion')->once();

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);

        $this->authService->refreshTokens($rawRefreshToken, $deviceId);
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function makeCredentials(array $overrides = []): LoginCredentialsDto
    {
        return LoginCredentialsDto::fromArray(array_merge([
            'email'      => 'test@example.com',
            'password'   => 'password123',
            'tenant_id'  => 'tenant-uuid-123',
            'device_id'  => 'device-uuid-456',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test',
        ], $overrides));
    }

    private function makeUser(array $attributes = []): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id                    = 'user-uuid-789';
        $user->tenant_id             = 'tenant-uuid-123';
        $user->organisation_id       = null;
        $user->branch_id             = null;
        $user->location_id           = null;
        $user->department_id         = null;
        $user->token_version         = 1;
        $user->is_active             = true;
        $user->is_locked             = false;
        $user->locked_until          = null;
        $user->failed_login_attempts = 0;
        $user->password              = Hash::make('password123');

        foreach ($attributes as $key => $value) {
            $user->{$key} = $value;
        }

        $user->shouldReceive('isLocked')->andReturnUsing(function () use ($user) {
            return $user->is_locked && ($user->locked_until === null || $user->locked_until->isFuture());
        });

        return $user;
    }
}
