<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\JwtTokenService;
use App\Services\RevocationService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Predis\ClientInterface as RedisClient;

/**
 * Unit tests for JwtTokenService.
 *
 * Uses real RSA keys generated in memory for deterministic tests.
 * No Laravel application bootstrap is required — pure unit tests.
 */
final class JwtTokenServiceTest extends TestCase
{
    private MockInterface $redis;
    private RevocationService $revocationService;

    /** @var string Temporary private key PEM */
    private string $privateKeyPath;

    /** @var string Temporary public key PEM */
    private string $publicKeyPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Generate a temporary RSA 2048-bit key pair for testing.
        $key = openssl_pkey_new([
            'digest_alg'       => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($key, $privatePem);
        $details   = openssl_pkey_get_details($key);
        $publicPem = $details['key'];

        $this->privateKeyPath = tempnam(sys_get_temp_dir(), 'jwt_priv_') . '.pem';
        $this->publicKeyPath  = tempnam(sys_get_temp_dir(), 'jwt_pub_')  . '.pem';

        file_put_contents($this->privateKeyPath, $privatePem);
        file_put_contents($this->publicKeyPath, $publicPem);

        $this->redis             = Mockery::mock(RedisClient::class);
        $this->revocationService = new RevocationService($this->redis, 'revoke');
        // Note: JwtTokenService is instantiated in buildServiceWithKeys() after
        // config values are set, not here in setUp().
    }

    protected function tearDown(): void
    {
        Mockery::close();

        if (file_exists($this->privateKeyPath)) {
            unlink($this->privateKeyPath);
        }
        if (file_exists($this->publicKeyPath)) {
            unlink($this->publicKeyPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_issues_a_jwt_token_containing_expected_claims(): void
    {
        $service = $this->buildServiceWithKeys();

        $claims = [
            'user_id'       => 'user-uuid-123',
            'tenant_id'     => 'tenant-uuid-456',
            'roles'         => ['admin'],
            'permissions'   => ['orders.create'],
            'device_id'     => 'device-abc',
            'token_version' => 1,
        ];

        $token = $service->issue($claims, 900);

        self::assertIsString($token);
        self::assertStringContainsString('.', $token);

        $decoded = $service->decode($token);

        self::assertSame('user-uuid-123', $decoded['user_id']);
        self::assertSame('tenant-uuid-456', $decoded['tenant_id']);
        self::assertSame(['admin'], $decoded['roles']);
        self::assertSame('device-abc', $decoded['device_id']);
        self::assertArrayHasKey('jti', $decoded);
        self::assertArrayHasKey('iss', $decoded);
        self::assertArrayHasKey('exp', $decoded);
        self::assertArrayHasKey('iat', $decoded);
    }

    #[Test]
    public function it_verifies_a_valid_token(): void
    {
        $service = $this->buildServiceWithKeys();

        $claims = [
            'user_id'       => 'user-123',
            'tenant_id'     => 'tenant-456',
            'device_id'     => 'device-abc',
            'token_version' => 1,
        ];

        $token = $service->issue($claims, 900);

        // Redis checks — token not revoked.
        $decoded = $service->decode($token);
        $jti     = $decoded['jti'];
        $userId  = $decoded['user_id'];

        $this->redis->shouldReceive('exists')
            ->with("revoke:jti:{$jti}")
            ->andReturn(0);

        $this->redis->shouldReceive('get')
            ->with("revoke:user:{$userId}:version")
            ->andReturn('1');

        $this->redis->shouldReceive('exists')
            ->with("revoke:device:{$userId}:device-abc")
            ->andReturn(0);

        self::assertTrue($service->verify($token));
    }

    #[Test]
    public function it_rejects_a_token_whose_jti_is_revoked(): void
    {
        $service = $this->buildServiceWithKeys();

        $claims = [
            'user_id'       => 'user-123',
            'tenant_id'     => 'tenant-456',
            'device_id'     => 'device-abc',
            'token_version' => 1,
        ];

        $token   = $service->issue($claims, 900);
        $decoded = $service->decode($token);
        $jti     = $decoded['jti'];

        $this->redis->shouldReceive('exists')
            ->with("revoke:jti:{$jti}")
            ->andReturn(1); // JTI is revoked

        self::assertFalse($service->verify($token));
    }

    #[Test]
    public function it_rejects_a_token_with_stale_token_version(): void
    {
        $service = $this->buildServiceWithKeys();

        $claims = [
            'user_id'       => 'user-123',
            'tenant_id'     => 'tenant-456',
            'device_id'     => 'device-abc',
            'token_version' => 1, // issued at version 1
        ];

        $token   = $service->issue($claims, 900);
        $decoded = $service->decode($token);
        $jti     = $decoded['jti'];
        $userId  = $decoded['user_id'];

        $this->redis->shouldReceive('exists')
            ->with("revoke:jti:{$jti}")
            ->andReturn(0);

        $this->redis->shouldReceive('get')
            ->with("revoke:user:{$userId}:version")
            ->andReturn('2'); // current version is 2 — token is stale

        self::assertFalse($service->verify($token));
    }

    #[Test]
    public function it_revokes_a_token_by_jti(): void
    {
        $service = $this->buildServiceWithKeys();

        $claims = [
            'user_id'       => 'user-123',
            'tenant_id'     => 'tenant-456',
            'device_id'     => 'device-abc',
            'token_version' => 1,
        ];

        $token   = $service->issue($claims, 900);
        $decoded = $service->decode($token);
        $jti     = $decoded['jti'];

        $this->redis->shouldReceive('set')
            ->once()
            ->with("revoke:jti:{$jti}", '1');

        $this->redis->shouldReceive('expire')
            ->once()
            ->with("revoke:jti:{$jti}", Mockery::type('int'));

        self::assertTrue($service->revoke($token));
    }

    #[Test]
    public function it_returns_empty_array_when_decoding_invalid_token(): void
    {
        $service = $this->buildServiceWithKeys();

        $result = $service->decode('not.a.valid.jwt');

        self::assertSame([], $result);
    }

    #[Test]
    public function it_throws_on_unsupported_algorithm(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Unsupported JWT algorithm/');

        // Configure an unsupported algorithm before constructing the service.
        // The constructor calls buildConfiguration() → resolveSigner() which
        // throws InvalidArgumentException for unknown algorithm strings.
        config([
            'jwt.algorithm'         => 'ES256', // unsupported — triggers the match default branch
            'jwt.issuer'            => 'https://test.kv-enterprise.io',
            'jwt.access_token_ttl'  => 900,
            'jwt.private_key_path'  => $this->privateKeyPath,
            'jwt.public_key_path'   => $this->publicKeyPath,
            'jwt.private_key_passphrase' => '',
        ]);

        new JwtTokenService($this->revocationService);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a JwtTokenService wired to the temp key pair.
     */
    private function buildServiceWithKeys(): JwtTokenService
    {
        // Patch the config() calls by setting values on the in-memory config.
        config([
            'jwt.algorithm'         => 'RS256',
            'jwt.issuer'            => 'https://test.kv-enterprise.io',
            'jwt.access_token_ttl'  => 900,
            'jwt.private_key_path'  => $this->privateKeyPath,
            'jwt.public_key_path'   => $this->publicKeyPath,
            'jwt.private_key_passphrase' => '',
        ]);

        return new JwtTokenService($this->revocationService);
    }
}
