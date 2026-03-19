<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Services\TenantConfigServiceInterface;
use App\DTOs\TokenClaimsDto;
use App\Exceptions\TokenException;
use App\Services\TokenService;
use Mockery;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure for HMAC (HS256) in tests — no key files required
        config(['jwt.algo' => 'HS256', 'jwt.keys.secret' => 'test-secret']);

        $revocationRepository = Mockery::mock(\App\Contracts\Repositories\TokenRevocationRepositoryInterface::class);
        $revocationRepository->shouldReceive('isRevoked')->andReturn(false);

        $this->tokenService = new TokenService($revocationRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_hash_refresh_token_returns_consistent_sha256_hash(): void
    {
        $raw  = 'my-raw-refresh-token';
        $hash = $this->tokenService->hashRefreshToken($raw);

        $this->assertEquals(hash('sha256', $raw), $hash);
        $this->assertEquals(64, strlen($hash)); // SHA-256 hex = 64 chars
    }

    public function test_verify_refresh_token_returns_true_for_matching_hash(): void
    {
        $raw  = 'my-raw-refresh-token';
        $hash = $this->tokenService->hashRefreshToken($raw);

        $this->assertTrue($this->tokenService->verifyRefreshToken($raw, $hash));
    }

    public function test_verify_refresh_token_returns_false_for_tampered_token(): void
    {
        $raw  = 'my-raw-refresh-token';
        $hash = $this->tokenService->hashRefreshToken($raw);

        $this->assertFalse($this->tokenService->verifyRefreshToken('tampered-token', $hash));
    }

    public function test_get_remaining_ttl_returns_zero_for_expired_payload(): void
    {
        $payload = ['exp' => time() - 60]; // Expired 60s ago
        $this->assertEquals(0, $this->tokenService->getRemainingTtl($payload));
    }

    public function test_get_remaining_ttl_returns_positive_for_valid_payload(): void
    {
        $payload = ['exp' => time() + 900]; // 15 minutes from now
        $ttl = $this->tokenService->getRemainingTtl($payload);
        $this->assertGreaterThan(800, $ttl);
        $this->assertLessThanOrEqual(900, $ttl);
    }

    public function test_issue_refresh_token_returns_non_empty_string(): void
    {
        $token = $this->tokenService->issueRefreshToken('user-123', 'session-456');
        $this->assertNotEmpty($token);
        $this->assertGreaterThan(32, strlen($token));
    }

    public function test_refresh_token_is_unique_per_call(): void
    {
        $token1 = $this->tokenService->issueRefreshToken('user-123', 'session-456');
        $token2 = $this->tokenService->issueRefreshToken('user-123', 'session-456');
        $this->assertNotEquals($token1, $token2);
    }
}
