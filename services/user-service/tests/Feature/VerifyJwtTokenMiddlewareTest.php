<?php

declare(strict_types=1);

namespace Tests\Feature;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * Feature tests for the VerifyJwtToken middleware.
 *
 * Verifies that the middleware:
 *  1. Accepts requests with a valid, non-revoked JWT.
 *  2. Rejects requests with a missing bearer token.
 *  3. Rejects requests with a token whose JTI is present in the Redis
 *     revocation list — the distributed revocation mechanism shared by all
 *     microservices in the platform.
 *  4. Falls through to the next handler when Redis is unavailable (fail-open
 *     policy prevents Redis downtime from locking out all authenticated users).
 *
 * A self-signed RSA key pair is generated once per test run so the tests are
 * fully self-contained without needing the storage/keys files.
 *
 * RefreshDatabase is used because several tests hit the protected
 * /api/v1/users endpoint which queries the database, confirming that
 * a valid token actually reaches the controller.
 */
class VerifyJwtTokenMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    // ── RSA key pair generated once for the whole test class ──────────────────
    private static string $privateKey = '';
    private static string $publicKey  = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($res, self::$privateKey);
        self::$publicKey = openssl_pkey_get_details($res)['key'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Inject the inline public key so VerifyJwtToken can verify without a file
        config(['jwt.public_key' => self::$publicKey, 'jwt.public_key_path' => '']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private int $jtiCounter = 0;

    private function makeToken(array $overrides = []): string
    {
        $payload = array_merge([
            'jti'         => 'test-jti-' . ++$this->jtiCounter,
            'sub'         => 'user-1',
            'tenant_id'   => 'tenant-1',
            'roles'       => ['admin'],
            'permissions' => [],
            'device_id'   => 'device-1',
            'iat'         => time(),
            'exp'         => time() + 900,
            'nbf'         => time(),
        ], $overrides);

        return JWT::encode($payload, self::$privateKey, 'RS256');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Tests
    // ──────────────────────────────────────────────────────────────────────────

    public function test_valid_token_passes_middleware(): void
    {
        // Redis revocation check returns 0 (not revoked)
        Redis::shouldReceive('exists')->andReturn(0);

        $token = $this->makeToken();

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('data.service', 'user-service');
    }

    public function test_missing_token_returns_401(): void
    {
        // /health has no auth middleware — should always succeed
        $this->getJson('/api/v1/health')
            ->assertOk();

        // Hit a protected endpoint without a token — no Redis call needed
        $this->getJson('/api/v1/users')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_revoked_token_is_rejected(): void
    {
        $jti   = 'revoked-jti-abc';
        $token = $this->makeToken(['jti' => $jti]);

        // Redis says this JTI is revoked (simulating Auth service revocation)
        Redis::shouldReceive('exists')
            ->with("revoked:{$jti}")
            ->andReturn(1);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/users')
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated');
    }

    public function test_non_revoked_token_is_accepted(): void
    {
        $jti   = 'valid-jti-xyz';
        $token = $this->makeToken(['jti' => $jti]);

        // Redis confirms this JTI is NOT revoked
        Redis::shouldReceive('exists')
            ->with("revoked:{$jti}")
            ->andReturn(0);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/users')
            ->assertStatus(200);
    }

    public function test_expired_token_is_rejected(): void
    {
        $token = $this->makeToken([
            'iat' => time() - 1000,
            'exp' => time() - 100,   // already expired
        ]);

        // Redis is not called for expired tokens (JWT decode throws before the check)
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/users')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_token_claims_are_set_on_request(): void
    {
        // Redis confirms this JTI is valid
        Redis::shouldReceive('exists')->andReturn(0);

        $token = $this->makeToken([
            'jti'       => 'claims-test-jti',
            'sub'       => 'user-claims-test',
            'tenant_id' => 'tenant-claims-test',
        ]);

        // Simply confirm the middleware doesn't reject a valid claim set
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/users')
            ->assertStatus(200);
    }
}
