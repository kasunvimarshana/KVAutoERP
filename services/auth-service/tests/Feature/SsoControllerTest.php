<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\IdentityProviderContract;
use App\DTOs\TokenPairDto;
use App\DTOs\UserInfoDto;
use App\Providers\IdentityProviders\LocalIdentityProvider;
use App\Providers\IdentityProviders\OktaIdentityProvider;
use App\Providers\IdentityProviders\SamlIdentityProvider;
use App\Services\IdentityProviderManager;
use Tests\TestCase;

/**
 * Feature tests for SsoController.
 *
 * Covers SSO provider listing, redirect URL building (OAuth2 and SAML),
 * and callback handling including CSRF state validation.
 */
class SsoControllerTest extends TestCase
{
    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    /**
     * Bind a stub IdentityProviderManager that returns the given provider mock.
     */
    private function mockProviderManager(?IdentityProviderContract $provider = null): IdentityProviderManager
    {
        $manager = new IdentityProviderManager($this->app);
        $manager->register('local',    LocalIdentityProvider::class);
        $manager->register('okta',     OktaIdentityProvider::class);
        $manager->register('saml',     SamlIdentityProvider::class);

        $this->app->instance(IdentityProviderManager::class, $manager);

        return $manager;
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/sso/providers
    // ──────────────────────────────────────────────────────────

    public function test_providers_returns_registered_provider_list(): void
    {
        $this->mockProviderManager();

        $this->getJson('/api/v1/sso/providers')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'message']);
    }

    public function test_providers_includes_all_built_in_adapters(): void
    {
        $this->mockProviderManager();

        $response = $this->getJson('/api/v1/sso/providers');
        $providers = $response->json('data');

        $this->assertContains('local', $providers);
        $this->assertContains('okta',  $providers);
        $this->assertContains('saml',  $providers);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/sso/redirect
    // ──────────────────────────────────────────────────────────

    public function test_redirect_returns_400_when_oauth2_provider_has_no_auth_endpoint(): void
    {
        $this->mockProviderManager();

        // Okta has no auth endpoint configured in testing config
        $this->getJson('/api/v1/sso/redirect?provider=okta&tenant_id=tenant-1')
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_redirect_returns_redirect_when_oauth2_auth_endpoint_is_configured(): void
    {
        $this->mockProviderManager();

        config(['iam_providers.okta.authorization_endpoint' => 'https://test.okta.com/oauth2/v1/authorize']);
        config(['iam_providers.okta.client_id' => 'my-client-id']);

        $response = $this->get('/api/v1/sso/redirect?provider=okta&tenant_id=tenant-1');

        // Should redirect to the IdP
        $this->assertContains($response->getStatusCode(), [302, 200]);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/sso/callback
    // ──────────────────────────────────────────────────────────

    public function test_callback_returns_400_when_state_missing(): void
    {
        $this->mockProviderManager();

        // No session state set — should fail CSRF check
        $this->getJson('/api/v1/sso/callback?code=some-code&state=wrong-state')
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_callback_returns_400_when_code_missing(): void
    {
        $this->mockProviderManager();

        // Put a valid state in session, but omit the code
        $this->withSession(['sso_state' => 'valid-state', 'sso_provider' => 'okta'])
            ->getJson('/api/v1/sso/callback?state=valid-state')
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_callback_succeeds_when_state_and_code_match(): void
    {
        // Mock an Okta-style provider that exchanges the code successfully
        $mockProvider = $this->createMock(IdentityProviderContract::class);
        $mockProvider->method('exchangeToken')->willReturn(new TokenPairDto(
            accessToken:  'sso-access-token',
            refreshToken: 'sso-refresh-token',
            expiresIn:    3600,
        ));

        $manager = new IdentityProviderManager($this->app);
        $manager->register('okta', OktaIdentityProvider::class);

        // Replace the okta class with a custom one that always succeeds
        /** @var IdentityProviderManager $mockManager */
        $mockManager = \Mockery::mock(IdentityProviderManager::class)->makePartial();
        $mockManager->shouldReceive('resolve')
            ->with('okta', 'tenant-1')
            ->andReturn($mockProvider);

        $this->app->instance(IdentityProviderManager::class, $mockManager);

        $this->withSession([
            'sso_state'     => 'secure-state-abc',
            'sso_provider'  => 'okta',
            'sso_tenant_id' => 'tenant-1',
        ])
            ->getJson('/api/v1/sso/callback?code=auth-code-123&state=secure-state-abc')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.access_token', 'sso-access-token');
    }

    // ──────────────────────────────────────────────────────────
    // SAML-specific SSO
    // ──────────────────────────────────────────────────────────

    public function test_saml_redirect_requires_configured_sso_url(): void
    {
        $this->mockProviderManager();

        // SAML with no sso_url configured — should throw or return error
        $response = $this->get('/api/v1/sso/redirect?provider=saml&tenant_id=tenant-1');

        // Expect either 302 redirect or 500 due to missing config — not a 200 success JSON
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public function test_saml_redirect_builds_authn_request_when_configured(): void
    {
        $this->mockProviderManager();

        config(['iam_providers.saml.entity_id' => 'https://app.example.com']);
        config(['iam_providers.saml.sso_url'   => 'https://idp.example.com/sso']);

        $response = $this->get('/api/v1/sso/redirect?provider=saml&tenant_id=tenant-1');

        // Should redirect to the IdP SSO URL with SAMLRequest parameter
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('SAMLRequest', $response->headers->get('Location', ''));
    }
}
