<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityProviders;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\IdentityProviders\LocalIdentityProvider;
use App\Models\User;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for LocalIdentityProvider.
 *
 * Verifies credential validation against the local auth-service database.
 */
final class LocalIdentityProviderTest extends TestCase
{
    private const TENANT_ID = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    private MockInterface $userRepository;
    private LocalIdentityProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->provider       = new LocalIdentityProvider($this->userRepository);
    }

    #[Test]
    public function it_authenticates_valid_credentials(): void
    {
        $hashedPassword = password_hash('ValidPass123!', PASSWORD_ARGON2ID);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('isActive')->andReturn(true);
        $user->shouldAllowMockingProtectedMethods();

        // Simulate Eloquent attribute access.
        $user->id              = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
        $user->email           = 'john@example.com';
        $user->password        = $hashedPassword;
        $user->tenant_id       = self::TENANT_ID;
        $user->organization_id = null;
        $user->branch_id       = null;

        $this->userRepository
            ->shouldReceive('findByEmailAndTenant')
            ->with('john@example.com', self::TENANT_ID)
            ->andReturn($user);

        $result = $this->provider->authenticate('john@example.com', 'ValidPass123!', self::TENANT_ID);

        $this->assertIsArray($result);
        $this->assertSame('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', $result['user_id']);
        $this->assertSame(self::TENANT_ID, $result['tenant_id']);
        $this->assertSame('local', $result['provider']);
    }

    #[Test]
    public function it_returns_null_for_wrong_password(): void
    {
        $hashedPassword = password_hash('RealPassword!', PASSWORD_ARGON2ID);

        $user           = Mockery::mock(User::class);
        $user->password = $hashedPassword;

        $this->userRepository
            ->shouldReceive('findByEmailAndTenant')
            ->andReturn($user);

        $result = $this->provider->authenticate('john@example.com', 'WrongPassword!', self::TENANT_ID);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('findByEmailAndTenant')
            ->andReturn(null);

        $result = $this->provider->authenticate('nobody@example.com', 'Pass!', self::TENANT_ID);

        $this->assertNull($result);
    }

    #[Test]
    public function it_reports_provider_name_as_local(): void
    {
        $this->assertSame('local', $this->provider->getProviderName());
    }

    #[Test]
    public function it_supports_all_tenants(): void
    {
        $this->assertTrue($this->provider->supports('any-tenant-id'));
        $this->assertTrue($this->provider->supports(''));
        $this->assertTrue($this->provider->supports(self::TENANT_ID));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
