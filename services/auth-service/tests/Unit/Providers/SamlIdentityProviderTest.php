<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Exceptions\AuthenticationException;
use App\Providers\IdentityProviders\SamlIdentityProvider;
use Tests\TestCase;

class SamlIdentityProviderTest extends TestCase
{
    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    /** Build a minimal valid SAMLResponse base64 string. */
    private function buildSamlResponse(
        string $nameId     = 'user@example.com',
        string $statusCode = 'urn:oasis:names:tc:SAML:2.0:status:Success',
        array  $attributes = [],
    ): string {
        $attrXml = '';

        foreach ($attributes as $name => $value) {
            $attrXml .= <<<XML
<saml:Attribute Name="{$name}">
    <saml:AttributeValue>{$value}</saml:AttributeValue>
</saml:Attribute>
XML;
        }

        $xml = <<<XML
<samlp:Response
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_resp1" Version="2.0" IssueInstant="2024-01-01T00:00:00Z">
    <samlp:Status>
        <samlp:StatusCode Value="{$statusCode}"/>
    </samlp:Status>
    <saml:Assertion>
        <saml:Subject>
            <saml:NameID>{$nameId}</saml:NameID>
        </saml:Subject>
        <saml:AttributeStatement>
            {$attrXml}
        </saml:AttributeStatement>
    </saml:Assertion>
</samlp:Response>
XML;

        return base64_encode($xml);
    }

    // ──────────────────────────────────────────────────────────
    // authenticate()
    // ──────────────────────────────────────────────────────────

    public function test_authenticate_requires_saml_response(): void
    {
        $provider = new SamlIdentityProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('SAML response is required');

        $provider->authenticate([]);
    }

    public function test_authenticate_parses_valid_saml_response(): void
    {
        $saml     = $this->buildSamlResponse('john@example.com');
        $provider = new SamlIdentityProvider();

        $result = $provider->authenticate(['saml_response' => $saml]);

        // AuthResultDto claims carry the parsed user info
        $this->assertNotEmpty($result->claims);
    }

    // ──────────────────────────────────────────────────────────
    // exchangeToken()
    // ──────────────────────────────────────────────────────────

    public function test_exchange_token_returns_token_pair_with_name_id(): void
    {
        $saml     = $this->buildSamlResponse('user@example.com');
        $provider = new SamlIdentityProvider();

        $tokenPair = $provider->exchangeToken($saml, 'https://sp.example.com/acs');

        $this->assertSame('user@example.com', $tokenPair->accessToken);
        $this->assertSame('', $tokenPair->refreshToken);
    }

    // ──────────────────────────────────────────────────────────
    // getUserInfo()
    // ──────────────────────────────────────────────────────────

    public function test_get_user_info_extracts_email_attribute(): void
    {
        $saml     = $this->buildSamlResponse(
            nameId:     'uid-001',
            attributes: ['email' => 'jane@example.com', 'displayName' => 'Jane Doe'],
        );
        $provider = new SamlIdentityProvider();

        // exchangeToken parses the assertion and caches attributes
        $tokenPair = $provider->exchangeToken($saml, 'https://sp.example.com/acs');
        $userInfo  = $provider->getUserInfo($tokenPair->accessToken);

        $this->assertSame('uid-001', $userInfo->externalId);
        $this->assertSame('jane@example.com', $userInfo->email);
        $this->assertSame('Jane Doe', $userInfo->name);
        $this->assertSame('saml', $userInfo->provider);
    }

    public function test_get_user_info_falls_back_to_name_id_for_email(): void
    {
        $saml     = $this->buildSamlResponse('noemail@example.com');
        $provider = new SamlIdentityProvider();

        $tokenPair = $provider->exchangeToken($saml, '');
        $userInfo  = $provider->getUserInfo($tokenPair->accessToken);

        $this->assertSame('noemail@example.com', $userInfo->externalId);
        $this->assertSame('noemail@example.com', $userInfo->email);
    }

    // ──────────────────────────────────────────────────────────
    // refreshToken()
    // ──────────────────────────────────────────────────────────

    public function test_refresh_token_throws(): void
    {
        $provider = new SamlIdentityProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('SAML provider does not support token refresh');

        $provider->refreshToken('any-token');
    }

    // ──────────────────────────────────────────────────────────
    // Metadata helpers
    // ──────────────────────────────────────────────────────────

    public function test_provider_name_is_saml(): void
    {
        $this->assertSame('saml', (new SamlIdentityProvider())->getProviderName());
    }

    public function test_supports_sso_is_true(): void
    {
        $this->assertTrue((new SamlIdentityProvider())->supportsSSO());
    }

    // ──────────────────────────────────────────────────────────
    // buildAuthnRequestUrl()
    // ──────────────────────────────────────────────────────────

    public function test_build_authn_request_url_contains_sso_url(): void
    {
        $provider = new SamlIdentityProvider([
            'entity_id' => 'https://sp.example.com',
            'sso_url'   => 'https://idp.example.com/sso',
        ]);

        $url = $provider->buildAuthnRequestUrl('https://sp.example.com/acs', 'state-abc');

        $this->assertStringContainsString('https://idp.example.com/sso', $url);
        $this->assertStringContainsString('SAMLRequest=', $url);
        $this->assertStringContainsString('RelayState=', $url);
    }

    public function test_build_authn_request_url_throws_without_sso_url(): void
    {
        $provider = new SamlIdentityProvider(['entity_id' => 'sp-id']); // no sso_url

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('SAML SSO URL is not configured');

        $provider->buildAuthnRequestUrl('https://sp.example.com/acs');
    }

    // ──────────────────────────────────────────────────────────
    // Error handling
    // ──────────────────────────────────────────────────────────

    public function test_exchange_token_throws_on_invalid_base64(): void
    {
        $provider = new SamlIdentityProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid SAML response encoding');

        $provider->exchangeToken('!!!not-base64!!!', '');
    }

    public function test_exchange_token_throws_on_failed_status(): void
    {
        $saml = $this->buildSamlResponse(
            statusCode: 'urn:oasis:names:tc:SAML:2.0:status:AuthnFailed',
        );
        $provider = new SamlIdentityProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('SAML authentication failed');

        $provider->exchangeToken($saml, '');
    }

    public function test_exchange_token_throws_when_name_id_missing(): void
    {
        $xml = <<<XML
<samlp:Response
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_resp2" Version="2.0" IssueInstant="2024-01-01T00:00:00Z">
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
    <saml:Assertion>
        <saml:Subject/>
    </saml:Assertion>
</samlp:Response>
XML;
        $provider = new SamlIdentityProvider();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('SAML response does not contain a NameID');

        $provider->exchangeToken(base64_encode($xml), '');
    }
}
