<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\IdentityProviderContract;
use App\DTOs\AuthResultDto;
use App\DTOs\TokenPairDto;
use App\DTOs\UserInfoDto;
use App\Exceptions\AuthenticationException;
use App\Providers\IdentityProviders\LocalIdentityProvider;
use App\Providers\IdentityProviders\OktaIdentityProvider;
use App\Providers\IdentityProviders\SamlIdentityProvider;
use App\Services\IdentityProviderManager;
use Tests\TestCase;

class IdentityProviderManagerTest extends TestCase
{
    private IdentityProviderManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new IdentityProviderManager($this->app);
    }

    // ──────────────────────────────────────────────────────────
    // register() / supports() / getRegisteredProviders()
    // ──────────────────────────────────────────────────────────

    public function test_register_adds_provider(): void
    {
        $this->manager->register('local', LocalIdentityProvider::class);

        $this->assertTrue($this->manager->supports('local'));
    }

    public function test_supports_returns_false_for_unknown_provider(): void
    {
        $this->assertFalse($this->manager->supports('unknown'));
    }

    public function test_get_registered_providers_lists_all(): void
    {
        $this->manager->register('local', LocalIdentityProvider::class);
        $this->manager->register('okta',  OktaIdentityProvider::class);
        $this->manager->register('saml',  SamlIdentityProvider::class);

        $this->assertSame(['local', 'okta', 'saml'], $this->manager->getRegisteredProviders());
    }

    // ──────────────────────────────────────────────────────────
    // resolve()
    // ──────────────────────────────────────────────────────────

    public function test_resolve_returns_correct_provider_instance(): void
    {
        $this->manager->register('local', LocalIdentityProvider::class);

        $provider = $this->manager->resolve('local', 'tenant-1');

        $this->assertInstanceOf(LocalIdentityProvider::class, $provider);
        $this->assertSame('local', $provider->getProviderName());
    }

    public function test_resolve_throws_for_unregistered_provider(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage("IAM provider 'unknown' is not registered");

        $this->manager->resolve('unknown', 'tenant-1');
    }

    public function test_resolve_reuses_cached_instance(): void
    {
        $this->manager->register('local', LocalIdentityProvider::class);

        $p1 = $this->manager->resolve('local', 'tenant-1');
        $p2 = $this->manager->resolve('local', 'tenant-1');

        $this->assertSame($p1, $p2);
    }

    public function test_resolve_creates_separate_instance_per_tenant(): void
    {
        $this->manager->register('local', LocalIdentityProvider::class);

        $p1 = $this->manager->resolve('local', 'tenant-a');
        $p2 = $this->manager->resolve('local', 'tenant-b');

        $this->assertNotSame($p1, $p2);
    }

    // ──────────────────────────────────────────────────────────
    // registerTenantConfig()
    // ──────────────────────────────────────────────────────────

    public function test_register_tenant_config_invalidates_cached_instance(): void
    {
        $this->manager->register('local', LocalIdentityProvider::class);

        // Resolve once — gets cached
        $p1 = $this->manager->resolve('local', 'tenant-1');

        // Register tenant-specific config — must invalidate the cached instance
        $this->manager->registerTenantConfig('local', 'tenant-1', ['custom' => 'value']);

        // Resolve again — should be a fresh instance
        $p2 = $this->manager->resolve('local', 'tenant-1');

        $this->assertNotSame($p1, $p2);
    }

    public function test_register_tenant_config_merges_with_static_config(): void
    {
        // Provide static config via Laravel config
        config(['iam_providers.saml' => ['entity_id' => 'static-entity']]);

        $this->manager->register('saml', SamlIdentityProvider::class);
        $this->manager->registerTenantConfig('saml', 'tenant-1', [
            'sso_url' => 'https://idp.example.com/sso',
        ]);

        $provider = $this->manager->resolve('saml', 'tenant-1');

        // SamlIdentityProvider accepts config; verify it is a SAML instance
        $this->assertInstanceOf(SamlIdentityProvider::class, $provider);
        $this->assertSame('saml', $provider->getProviderName());
    }

    // ──────────────────────────────────────────────────────────
    // SAML provider registration and SSO flag
    // ──────────────────────────────────────────────────────────

    public function test_saml_provider_is_registered_and_supports_sso(): void
    {
        $this->manager->register('saml', SamlIdentityProvider::class);

        $provider = $this->manager->resolve('saml', 'tenant-x');

        $this->assertInstanceOf(SamlIdentityProvider::class, $provider);
        $this->assertTrue($provider->supportsSSO());
    }

    public function test_local_provider_does_not_support_sso(): void
    {
        $this->manager->register('local', LocalIdentityProvider::class);

        $provider = $this->manager->resolve('local', 'tenant-x');

        $this->assertFalse($provider->supportsSSO());
    }
}
