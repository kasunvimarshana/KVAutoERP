<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityProviders;

use App\Contracts\IdentityProvider\IdentityProviderInterface;
use App\IdentityProviders\IdentityProviderFactory;
use App\IdentityProviders\LocalIdentityProvider;
use App\IdentityProviders\OAuthIdentityProvider;
use Illuminate\Container\Container;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for IdentityProviderFactory.
 *
 * Verifies tenant-aware provider resolution, fallback behaviour,
 * and dynamic provider registration.
 */
final class IdentityProviderFactoryTest extends TestCase
{
    private const TENANT_ID = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    #[Test]
    public function it_resolves_local_provider_by_default(): void
    {
        $localProvider = Mockery::mock(IdentityProviderInterface::class);
        $localProvider->shouldReceive('supports')->andReturn(true);
        $localProvider->shouldReceive('getProviderName')->andReturn('local');

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('make')
            ->with(LocalIdentityProvider::class)
            ->andReturn($localProvider);

        config(['auth_service.tenant_providers' => []]);

        $factory = new IdentityProviderFactory($container);
        $factory->registerProvider('local', LocalIdentityProvider::class);

        $resolved = $factory->resolveForTenant(self::TENANT_ID);

        $this->assertSame($localProvider, $resolved);
    }

    #[Test]
    public function it_resolves_configured_provider_for_tenant(): void
    {
        $oauthProvider = Mockery::mock(IdentityProviderInterface::class);
        $oauthProvider->shouldReceive('supports')->andReturn(true);

        $localProvider = Mockery::mock(IdentityProviderInterface::class);
        $localProvider->shouldReceive('supports')->andReturn(true);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('make')
            ->with(OAuthIdentityProvider::class)
            ->andReturn($oauthProvider);

        config([
            'auth_service.tenant_providers' => [
                self::TENANT_ID => 'oauth2',
            ],
        ]);

        $factory = new IdentityProviderFactory($container);
        $factory->registerProvider('local', LocalIdentityProvider::class);
        $factory->registerProvider('oauth2', OAuthIdentityProvider::class);

        $resolved = $factory->resolveForTenant(self::TENANT_ID);

        $this->assertSame($oauthProvider, $resolved);
    }

    #[Test]
    public function it_falls_back_to_local_when_configured_provider_does_not_support_tenant(): void
    {
        $oauthProvider = Mockery::mock(IdentityProviderInterface::class);
        $oauthProvider->shouldReceive('supports')->andReturn(false);

        $localProvider = Mockery::mock(IdentityProviderInterface::class);
        $localProvider->shouldReceive('supports')->andReturn(true);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('make')
            ->with(OAuthIdentityProvider::class)
            ->andReturn($oauthProvider);
        $container->shouldReceive('make')
            ->with(LocalIdentityProvider::class)
            ->andReturn($localProvider);

        config([
            'auth_service.tenant_providers' => [
                self::TENANT_ID => 'oauth2',
            ],
        ]);

        $factory = new IdentityProviderFactory($container);
        $factory->registerProvider('local', LocalIdentityProvider::class);
        $factory->registerProvider('oauth2', OAuthIdentityProvider::class);

        $resolved = $factory->resolveForTenant(self::TENANT_ID);

        $this->assertSame($localProvider, $resolved);
    }

    #[Test]
    public function it_returns_all_registered_provider_names(): void
    {
        $container = Mockery::mock(Container::class);

        $factory = new IdentityProviderFactory($container);
        $factory->registerProvider('local', LocalIdentityProvider::class);
        $factory->registerProvider('oauth2', OAuthIdentityProvider::class);

        $this->assertSame(['local', 'oauth2'], $factory->getRegisteredProviders());
    }

    #[Test]
    public function it_allows_fluent_provider_registration(): void
    {
        $container = Mockery::mock(Container::class);

        $factory = new IdentityProviderFactory($container);

        $result = $factory
            ->registerProvider('local', LocalIdentityProvider::class)
            ->registerProvider('oauth2', OAuthIdentityProvider::class);

        $this->assertSame($factory, $result);
        $this->assertCount(2, $factory->getRegisteredProviders());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
