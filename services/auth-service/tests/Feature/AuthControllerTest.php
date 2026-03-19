<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AuthServiceContract;
use App\DTOs\AuthResultDto;
use App\DTOs\TokenClaimsDto;
use App\DTOs\TokenPairDto;
use App\Exceptions\AuthenticationException;
use App\Exceptions\TokenException;
use Tests\TestCase;

/**
 * Feature tests for the AuthController endpoints.
 *
 * All dependencies are mocked so the tests remain fast and deterministic
 * without requiring Redis, a real User service, or JWT keys.
 */
class AuthControllerTest extends TestCase
{
    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function mockAuthService(): \Mockery\MockInterface
    {
        return $this->mock(AuthServiceContract::class);
    }

    private function makeAuthResult(): AuthResultDto
    {
        return new AuthResultDto(
            accessToken:  'access-token-value',
            refreshToken: 'refresh-token-value',
            expiresIn:    900,
            tokenType:    'Bearer',
            claims:       ['sub' => 'user-1', 'tenant_id' => 'tenant-1'],
        );
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/health
    // ──────────────────────────────────────────────────────────

    public function test_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'auth-service');
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/auth/login
    // ──────────────────────────────────────────────────────────

    public function test_login_returns_tokens_on_success(): void
    {
        $this->mockAuthService()
            ->shouldReceive('login')
            ->once()
            ->andReturn($this->makeAuthResult());

        $this->postJson('/api/v1/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.access_token', 'access-token-value')
            ->assertJsonPath('data.refresh_token', 'refresh-token-value')
            ->assertJsonPath('data.token_type', 'Bearer');
    }

    public function test_login_returns_422_for_missing_email(): void
    {
        // provider=local requires email; omitting it triggers validation error
        $this->postJson('/api/v1/auth/login', [
            'provider' => 'local',
            'password' => 'secret',
        ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_login_returns_401_on_invalid_credentials(): void
    {
        $this->mockAuthService()
            ->shouldReceive('login')
            ->once()
            ->andThrow(new AuthenticationException('Invalid credentials'));

        $this->postJson('/api/v1/auth/login', [
            'email'    => 'bad@example.com',
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/auth/refresh
    // ──────────────────────────────────────────────────────────

    public function test_refresh_returns_new_token_pair(): void
    {
        $this->mockAuthService()
            ->shouldReceive('refreshToken')
            ->once()
            ->andReturn(new TokenPairDto(
                accessToken:  'new-access',
                refreshToken: 'new-refresh',
                expiresIn:    900,
            ));

        $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => str_repeat('a', 64), // min:32 required
        ])
            ->assertOk()
            ->assertJsonPath('data.access_token', 'new-access')
            ->assertJsonPath('data.refresh_token', 'new-refresh');
    }

    public function test_refresh_returns_401_for_invalid_token(): void
    {
        $this->mockAuthService()
            ->shouldReceive('refreshToken')
            ->once()
            ->andThrow(new TokenException('Invalid or expired refresh token'));

        $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => str_repeat('b', 64), // min:32 — passes validation
        ])->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/auth/public-key
    // ──────────────────────────────────────────────────────────

    public function test_public_key_endpoint_returns_pem_key(): void
    {
        // Generate an RSA key pair for the test
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $details = openssl_pkey_get_details($key);
        $publicKey = $details['key'];

        config([
            'jwt.public_key_path' => null,
            'jwt.public_key'      => $publicKey,
        ]);

        $this->getJson('/api/v1/auth/public-key')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.algorithm', 'RS256');
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/auth/service-token
    // ──────────────────────────────────────────────────────────

    public function test_service_token_endpoint_issues_token(): void
    {
        $this->mockAuthService()
            ->shouldReceive('issueServiceToken')
            ->once()
            ->with('user-service', 'my-super-secret-key-here')
            ->andReturn(new TokenPairDto(
                accessToken:  'service-access-token',
                refreshToken: 'service-refresh-token',
                expiresIn:    3600,
            ));

        $this->postJson('/api/v1/auth/service-token', [
            'service_id'     => 'user-service',
            'service_secret' => 'my-super-secret-key-here',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.access_token', 'service-access-token')
            ->assertJsonPath('data.expires_in', 3600);
    }

    public function test_service_token_returns_422_for_short_secret(): void
    {
        $this->postJson('/api/v1/auth/service-token', [
            'service_id'     => 'user-service',
            'service_secret' => 'short',
        ])->assertUnprocessable()
          ->assertJsonStructure(['message', 'errors']);
    }

    public function test_service_token_returns_401_for_invalid_credentials(): void
    {
        $this->mockAuthService()
            ->shouldReceive('issueServiceToken')
            ->once()
            ->andThrow(new AuthenticationException('Invalid service credentials'));

        $this->postJson('/api/v1/auth/service-token', [
            'service_id'     => 'fake-service',
            'service_secret' => 'wrong-secret-value-here',
        ])->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/sso/providers
    // ──────────────────────────────────────────────────────────

    public function test_sso_providers_endpoint_returns_list(): void
    {
        $this->getJson('/api/v1/sso/providers')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
