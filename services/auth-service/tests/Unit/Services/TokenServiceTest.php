<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\TokenClaimsDto;
use App\Exceptions\TokenException;
use App\Services\TokenService;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    private string $tmpDir;
    private string $privatePath;
    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpDir     = sys_get_temp_dir() . '/kv-jwt-test-' . uniqid();
        mkdir($this->tmpDir, 0700, true);

        $this->privatePath = $this->tmpDir . '/private.pem';
        $this->publicPath  = $this->tmpDir . '/public.pem';

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $pem);
        file_put_contents($this->privatePath, $pem);

        $details = openssl_pkey_get_details($key);
        file_put_contents($this->publicPath, $details['key']);

        config([
            'jwt.private_key_path' => $this->privatePath,
            'jwt.public_key_path'  => $this->publicPath,
            'jwt.issuer'           => 'test-issuer',
            'jwt.ttl'              => 900,
            'jwt.refresh_ttl'      => 2592000,
        ]);
    }

    protected function tearDown(): void
    {
        @unlink($this->privatePath);
        @unlink($this->publicPath);
        @rmdir($this->tmpDir);
        parent::tearDown();
    }

    private function makeService(): TokenService
    {
        return new TokenService();
    }

    public function test_issues_a_valid_jwt(): void
    {
        $svc   = $this->makeService();
        $token = $svc->issue(['sub' => 'user-1', 'tenant_id' => 'tenant-a'], 900);

        $this->assertIsString($token);
        $this->assertCount(3, explode('.', $token));
    }

    public function test_verifies_a_valid_token_and_returns_claims(): void
    {
        Redis::shouldReceive('exists')->once()->andReturn(0);

        $svc    = $this->makeService();
        $token  = $svc->issue(['sub' => 'user-1', 'tenant_id' => 'tenant-a', 'jti' => 'abc-123'], 900);
        $claims = $svc->verify($token);

        $this->assertSame('user-1', $claims['sub']);
        $this->assertSame('tenant-a', $claims['tenant_id']);
        $this->assertSame('abc-123', $claims['jti']);
    }

    public function test_returns_public_key(): void
    {
        $svc = $this->makeService();
        $key = $svc->getPublicKey();

        $this->assertStringContainsString('BEGIN PUBLIC KEY', $key);
    }

    public function test_builds_standard_claims_from_user_array(): void
    {
        $svc = $this->makeService();

        $user = [
            'id'              => 'u-1',
            'tenant_id'       => 'tenant-1',
            'organization_id' => 'org-1',
            'branch_id'       => 'branch-1',
            'roles'           => ['admin'],
            'permissions'     => ['users.read'],
            'token_version'   => 3,
            'iam_provider'    => 'local',
        ];

        $claims = $svc->buildClaims($user, 'device-1', 'tenant-1');

        $this->assertSame('u-1', $claims['sub']);
        $this->assertSame('tenant-1', $claims['tenant_id']);
        $this->assertSame(['admin'], $claims['roles']);
        $this->assertSame('device-1', $claims['device_id']);
        $this->assertSame(3, $claims['token_version']);
    }

    public function test_decodes_without_verification(): void
    {
        $svc    = $this->makeService();
        $token  = $svc->issue(['sub' => 'user-2', 'jti' => 'xyz'], 900);
        $claims = $svc->decode($token, false);

        $this->assertSame('user-2', $claims['sub']);
        $this->assertSame('xyz', $claims['jti']);
    }

    public function test_verify_throws_for_revoked_token(): void
    {
        Redis::shouldReceive('exists')->once()->andReturn(1); // revoked

        $svc   = $this->makeService();
        $token = $svc->issue(['sub' => 'user-3', 'jti' => 'revoked-jti'], 900);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token has been revoked');

        $svc->verify($token);
    }

    public function test_revoke_sets_redis_key(): void
    {
        Redis::shouldReceive('setex')->once()->with(
            \Mockery::pattern('/^revoked:/'),
            \Mockery::type('int'),
            '1',
        );

        $svc = $this->makeService();
        $svc->revoke('some-jti');
    }

    public function test_revoke_is_noop_for_empty_jti(): void
    {
        Redis::shouldReceive('setex')->never();

        $svc = $this->makeService();
        $svc->revoke('');

        $this->addToAssertionCount(1);
    }

    public function test_is_revoked_returns_false_for_unknown_jti(): void
    {
        Redis::shouldReceive('exists')->once()->andReturn(0);

        $svc = $this->makeService();

        $this->assertFalse($svc->isRevoked('unknown-jti'));
    }

    public function test_decode_throws_for_invalid_token_format(): void
    {
        $svc = $this->makeService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid token format');

        $svc->decode('not.a.valid.token.here', false);
    }
}
