<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Contracts\TokenServiceContract;
use App\Contracts\UserServiceClientContract;
use App\DTOs\TokenPairDto;
use App\DTOs\UserDto;
use App\Exceptions\AuthenticationException;
use App\Providers\IdentityProviders\LocalIdentityProvider;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * Unit tests for LocalIdentityProvider.
 *
 * All external collaborators (UserServiceClient, TokenService, RevocationService,
 * Redis) are mocked so tests remain fast and fully deterministic.
 */
class LocalIdentityProviderTest extends TestCase
{
    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function makeProvider(
        ?UserServiceClientContract $userClient   = null,
        ?TokenServiceContract      $tokenService = null,
        array                      $config       = [],
    ): LocalIdentityProvider {
        return new LocalIdentityProvider(
            userServiceClient: $userClient   ?? $this->mock(UserServiceClientContract::class),
            tokenService:      $tokenService ?? $this->mock(TokenServiceContract::class),
            config:            $config,
        );
    }

    private function makeActiveUser(): UserDto
    {
        return new UserDto(
            id:             'user-uuid-1',
            email:          'user@example.com',
            name:           'Test User',
            tenantId:       'tenant-uuid-1',
            organizationId: 'org-uuid-1',
            branchId:       'branch-uuid-1',
            status:         'active',
            roles:          ['user'],
            permissions:    ['read'],
            tokenVersion:   1,
            iamProvider:    'local',
        );
    }

    // ──────────────────────────────────────────────────────────
    // getProviderName() / supportsSSO()
    // ──────────────────────────────────────────────────────────

    public function test_provider_name_is_local(): void
    {
        $this->assertSame('local', $this->makeProvider()->getProviderName());
    }

    public function test_does_not_support_sso(): void
    {
        $this->assertFalse($this->makeProvider()->supportsSSO());
    }

    // ──────────────────────────────────────────────────────────
    // authenticate()
    // ──────────────────────────────────────────────────────────

    public function test_authenticate_returns_auth_result_on_valid_credentials(): void
    {
        $user         = $this->makeActiveUser();
        $userClient   = $this->mock(UserServiceClientContract::class);
        $tokenService = $this->mock(TokenServiceContract::class);

        $userClient->shouldReceive('findByEmail')->with('user@example.com')->andReturn($user);
        $userClient->shouldReceive('validateCredentials')->with('user-uuid-1', 'secret123')->andReturn(true);
        $userClient->shouldReceive('recordLoginEvent')->once()->with('user-uuid-1', 'device-1', '127.0.0.1');

        $tokenService->shouldReceive('buildClaims')
            ->andReturn(['sub' => 'user-uuid-1', 'jti' => 'jti-1', 'tenant_id' => 'tenant-uuid-1']);
        $tokenService->shouldReceive('issue')->andReturn('access-token');
        $tokenService->shouldReceive('issueRefreshToken')->andReturn('refresh-token');

        $provider = $this->makeProvider($userClient, $tokenService);
        $result   = $provider->authenticate([
            'email'      => 'user@example.com',
            'password'   => 'secret123',
            'device_id'  => 'device-1',
            'tenant_id'  => 'tenant-uuid-1',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertSame('access-token', $result->accessToken);
        $this->assertSame('refresh-token', $result->refreshToken);
        $this->assertSame('Bearer', $result->tokenType);
        $this->assertGreaterThan(0, $result->expiresIn);
    }

    public function test_authenticate_uses_user_tenant_id_when_not_provided(): void
    {
        $user         = $this->makeActiveUser();
        $userClient   = $this->mock(UserServiceClientContract::class);
        $tokenService = $this->mock(TokenServiceContract::class);

        $userClient->shouldReceive('findByEmail')->andReturn($user);
        $userClient->shouldReceive('validateCredentials')->andReturn(true);
        $userClient->shouldReceive('recordLoginEvent')->once();

        // Verify buildClaims is called with the user's tenantId when no tenant_id in credentials
        $tokenService->shouldReceive('buildClaims')
            ->with($user->toArray(), 'device-1', 'tenant-uuid-1')
            ->andReturn(['sub' => 'user-uuid-1', 'jti' => 'jti-1']);
        $tokenService->shouldReceive('issue')->andReturn('access-token');
        $tokenService->shouldReceive('issueRefreshToken')->andReturn('refresh-token');

        $provider = $this->makeProvider($userClient, $tokenService);
        $provider->authenticate([
            'email'      => 'user@example.com',
            'password'   => 'secret123',
            'device_id'  => 'device-1',
            'ip_address' => '127.0.0.1',
        ]);

        // Mockery verifies the expectation on teardown
        $this->assertTrue(true);
    }

    public function test_authenticate_skips_login_event_when_ip_address_missing(): void
    {
        $user         = $this->makeActiveUser();
        $userClient   = $this->mock(UserServiceClientContract::class);
        $tokenService = $this->mock(TokenServiceContract::class);

        $userClient->shouldReceive('findByEmail')->andReturn($user);
        $userClient->shouldReceive('validateCredentials')->andReturn(true);
        $userClient->shouldNotReceive('recordLoginEvent');

        $tokenService->shouldReceive('buildClaims')->andReturn(['sub' => 'user-uuid-1', 'jti' => 'jti-1']);
        $tokenService->shouldReceive('issue')->andReturn('access-token');
        $tokenService->shouldReceive('issueRefreshToken')->andReturn('refresh-token');

        $provider = $this->makeProvider($userClient, $tokenService);
        $provider->authenticate(['email' => 'user@example.com', 'password' => 'secret123']);

        $this->assertTrue(true);
    }

    public function test_authenticate_throws_when_email_missing(): void
    {
        $provider = $this->makeProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Email and password are required');

        $provider->authenticate(['password' => 'secret']);
    }

    public function test_authenticate_throws_when_password_missing(): void
    {
        $provider = $this->makeProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Email and password are required');

        $provider->authenticate(['email' => 'user@example.com']);
    }

    public function test_authenticate_throws_when_user_not_found(): void
    {
        $userClient = $this->mock(UserServiceClientContract::class);
        $userClient->shouldReceive('findByEmail')->andReturn(null);

        $provider = $this->makeProvider($userClient);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $provider->authenticate(['email' => 'missing@example.com', 'password' => 'x']);
    }

    public function test_authenticate_throws_when_account_inactive(): void
    {
        $inactiveUser = new UserDto(
            id: 'user-2', email: 'inactive@example.com', name: 'Inactive',
            tenantId: 't1', organizationId: '', branchId: '',
            status: 'inactive', roles: [], permissions: [], tokenVersion: 1,
        );

        $userClient = $this->mock(UserServiceClientContract::class);
        $userClient->shouldReceive('findByEmail')->andReturn($inactiveUser);

        $provider = $this->makeProvider($userClient);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Account is not active');

        $provider->authenticate(['email' => 'inactive@example.com', 'password' => 'x']);
    }

    public function test_authenticate_throws_on_wrong_password(): void
    {
        $user       = $this->makeActiveUser();
        $userClient = $this->mock(UserServiceClientContract::class);
        $userClient->shouldReceive('findByEmail')->andReturn($user);
        $userClient->shouldReceive('validateCredentials')->andReturn(false);

        $provider = $this->makeProvider($userClient);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $provider->authenticate(['email' => 'user@example.com', 'password' => 'wrong']);
    }

    // ──────────────────────────────────────────────────────────
    // exchangeToken()
    // ──────────────────────────────────────────────────────────

    public function test_exchange_token_throws(): void
    {
        $provider = $this->makeProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Local provider does not support OAuth2 token exchange');

        $provider->exchangeToken('any-code', 'https://redirect.uri');
    }

    // ──────────────────────────────────────────────────────────
    // getUserInfo()
    // ──────────────────────────────────────────────────────────

    public function test_get_user_info_returns_user_data_from_jwt(): void
    {
        $user         = $this->makeActiveUser();
        $userClient   = $this->mock(UserServiceClientContract::class);
        $tokenService = $this->mock(TokenServiceContract::class);

        $tokenService->shouldReceive('decode')
            ->with('access-token-123', false)
            ->andReturn(['sub' => 'user-uuid-1', 'jti' => 'jti-1', 'tenant_id' => 'tenant-uuid-1']);

        $userClient->shouldReceive('findById')->with('user-uuid-1')->andReturn($user);

        $provider = $this->makeProvider($userClient, $tokenService);
        $userInfo = $provider->getUserInfo('access-token-123');

        $this->assertSame('user-uuid-1', $userInfo->externalId);
        $this->assertSame('user@example.com', $userInfo->email);
        $this->assertSame('Test User', $userInfo->name);
        $this->assertSame('local', $userInfo->provider);
    }

    public function test_get_user_info_handles_missing_user_gracefully(): void
    {
        $userClient   = $this->mock(UserServiceClientContract::class);
        $tokenService = $this->mock(TokenServiceContract::class);

        $tokenService->shouldReceive('decode')
            ->andReturn(['sub' => 'user-uuid-missing', 'jti' => 'jti-2']);

        $userClient->shouldReceive('findById')->andReturn(null);

        $provider = $this->makeProvider($userClient, $tokenService);
        $userInfo = $provider->getUserInfo('token-for-unknown-user');

        $this->assertSame('user-uuid-missing', $userInfo->externalId);
        $this->assertSame('', $userInfo->email);
        $this->assertSame('local', $userInfo->provider);
    }

    public function test_get_user_info_throws_on_invalid_token(): void
    {
        $tokenService = $this->mock(TokenServiceContract::class);
        $tokenService->shouldReceive('decode')->andThrow(new \RuntimeException('Invalid token'));

        $provider = $this->makeProvider(tokenService: $tokenService);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Failed to decode access token');

        $provider->getUserInfo('bad-token');
    }

    // ──────────────────────────────────────────────────────────
    // logout()
    // ──────────────────────────────────────────────────────────

    public function test_logout_revokes_token_jti(): void
    {
        $tokenService = $this->mock(TokenServiceContract::class);
        $tokenService->shouldReceive('decode')
            ->with('access-token-abc', false)
            ->andReturn(['jti' => 'jti-abc', 'sub' => 'user-1']);
        $tokenService->shouldReceive('revoke')->with('jti-abc')->once();

        $provider = $this->makeProvider(tokenService: $tokenService);
        $provider->logout('access-token-abc');

        // Mockery verifies revoke() was called once
        $this->assertTrue(true);
    }

    public function test_logout_is_non_fatal_on_decode_error(): void
    {
        $tokenService = $this->mock(TokenServiceContract::class);
        $tokenService->shouldReceive('decode')->andThrow(new \RuntimeException('bad token'));

        $provider = $this->makeProvider(tokenService: $tokenService);
        $provider->logout('bad-token'); // must not throw

        $this->assertTrue(true);
    }

    public function test_logout_does_not_revoke_when_jti_is_empty(): void
    {
        $tokenService = $this->mock(TokenServiceContract::class);
        $tokenService->shouldReceive('decode')
            ->andReturn(['sub' => 'user-1']); // no 'jti' key
        $tokenService->shouldNotReceive('revoke');

        $provider = $this->makeProvider(tokenService: $tokenService);
        $provider->logout('token-without-jti');

        $this->assertTrue(true);
    }

    // ──────────────────────────────────────────────────────────
    // refreshToken()
    // ──────────────────────────────────────────────────────────

    public function test_refresh_token_rotates_and_returns_new_pair(): void
    {
        $user         = $this->makeActiveUser();
        $userClient   = $this->mock(UserServiceClientContract::class);
        $tokenService = $this->mock(TokenServiceContract::class);

        Redis::shouldReceive('get')
            ->with('refresh:refresh-token-xyz')
            ->andReturn(json_encode(['user_id' => 'user-uuid-1', 'device_id' => 'device-1']));

        Redis::shouldReceive('del')
            ->with('refresh:refresh-token-xyz')
            ->once();

        $userClient->shouldReceive('findById')->with('user-uuid-1')->andReturn($user);

        $tokenService->shouldReceive('buildClaims')
            ->andReturn(['sub' => 'user-uuid-1', 'jti' => 'jti-new']);
        $tokenService->shouldReceive('issue')->andReturn('new-access-token');
        $tokenService->shouldReceive('issueRefreshToken')->andReturn('new-refresh-token');

        $provider = $this->makeProvider($userClient, $tokenService);
        $result   = $provider->refreshToken('refresh-token-xyz');

        $this->assertSame('new-access-token', $result->accessToken);
        $this->assertSame('new-refresh-token', $result->refreshToken);
        $this->assertGreaterThan(0, $result->expiresIn);
    }

    public function test_refresh_token_throws_for_invalid_token(): void
    {
        Redis::shouldReceive('get')->andReturn(null);

        $provider = $this->makeProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid or expired refresh token');

        $provider->refreshToken('bad-token');
    }

    public function test_refresh_token_throws_when_user_not_found(): void
    {
        Redis::shouldReceive('get')
            ->andReturn(json_encode(['user_id' => 'ghost-user', 'device_id' => 'device-1']));

        $userClient = $this->mock(UserServiceClientContract::class);
        $userClient->shouldReceive('findById')->andReturn(null);

        $provider = $this->makeProvider($userClient);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User not found or inactive');

        $provider->refreshToken('some-refresh-token');
    }

    public function test_refresh_token_throws_when_user_inactive(): void
    {
        $inactiveUser = new UserDto(
            id: 'user-2', email: 'inactive@example.com', name: 'Inactive',
            tenantId: 't1', organizationId: '', branchId: '',
            status: 'inactive', roles: [], permissions: [], tokenVersion: 1,
        );

        Redis::shouldReceive('get')
            ->andReturn(json_encode(['user_id' => 'user-2', 'device_id' => 'device-1']));

        $userClient = $this->mock(UserServiceClientContract::class);
        $userClient->shouldReceive('findById')->andReturn($inactiveUser);

        $provider = $this->makeProvider($userClient);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User not found or inactive');

        $provider->refreshToken('some-refresh-token');
    }
}
