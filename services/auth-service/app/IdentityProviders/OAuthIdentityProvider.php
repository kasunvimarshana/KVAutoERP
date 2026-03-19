<?php

declare(strict_types=1);

namespace App\IdentityProviders;

use App\Contracts\IdentityProvider\IdentityProviderInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;

/**
 * OAuth2 / OpenID Connect identity provider adapter.
 *
 * Supports any standards-compliant OAuth2/OIDC provider such as Okta,
 * Keycloak, Azure AD, Auth0, or a custom IdP. Credentials are exchanged
 * for an access token via the OAuth2 Resource Owner Password Credentials
 * (ROPC) grant, and user info is retrieved from the OIDC UserInfo endpoint.
 *
 * Each tenant may configure its own OAuth provider via the
 * `iam_providers` section of the auth_service config.
 *
 * Security note: The ROPC grant is a legacy flow. For new integrations
 * prefer the Authorization Code + PKCE flow where possible. ROPC is
 * supported here for enterprise scenarios where redirect-based flows
 * are not feasible (e.g., API clients, legacy ERP integrations).
 */
final class OAuthIdentityProvider implements IdentityProviderInterface
{
    /** @var array<string, array<string, string>> Tenant-keyed provider config. */
    private readonly array $tenantProviders;

    public function __construct(
        private readonly HttpFactory $http,
    ) {
        /** @var array<string, array<string, string>> $providers */
        $providers             = (array) config('auth_service.iam_providers', []);
        $this->tenantProviders = $providers;
    }

    /**
     * {@inheritDoc}
     *
     * Exchanges `email` + `password` for an OAuth2 token using the ROPC
     * grant, then calls the OIDC UserInfo endpoint to retrieve the user's
     * identity claims. Returns a normalised identity map on success.
     *
     * @param  string  $email
     * @param  string  $password
     * @param  string  $tenantId
     * @return array<string, mixed>|null
     */
    public function authenticate(string $email, string $password, string $tenantId): ?array
    {
        $config = $this->resolveProviderConfig($tenantId);

        if ($config === null) {
            return null;
        }

        try {
            $tokenResponse = $this->http
                ->timeout((int) ($config['timeout_seconds'] ?? 10))
                ->asForm()
                ->post($config['token_url'], [
                    'grant_type' => 'password',
                    'client_id'  => $config['client_id'],
                    'client_secret' => $config['client_secret'] ?? '',
                    'username'   => $email,
                    'password'   => $password,
                    'scope'      => $config['scope'] ?? 'openid profile email',
                ]);

            if (!$tokenResponse->successful()) {
                Log::warning('OAuthIdentityProvider: token exchange failed', [
                    'tenant_id' => $tenantId,
                    'status'    => $tokenResponse->status(),
                ]);

                return null;
            }

            /** @var array<string, mixed> $tokenData */
            $tokenData   = $tokenResponse->json();
            $accessToken = (string) ($tokenData['access_token'] ?? '');

            if ($accessToken === '') {
                return null;
            }

            $userInfo = $this->fetchUserInfo($config, $accessToken);

            if ($userInfo === null) {
                return null;
            }

            $sub = (string) ($userInfo['sub'] ?? '');

            if ($sub === '') {
                Log::warning('OAuthIdentityProvider: UserInfo response missing required sub claim', [
                    'tenant_id' => $tenantId,
                ]);

                return null;
            }

            return [
                'user_id'         => $sub,
                'email'           => (string) ($userInfo['email'] ?? $email),
                'tenant_id'       => $tenantId,
                'organization_id' => null,
                'branch_id'       => null,
                'is_active'       => true,
                'provider'        => $this->getProviderName(),
                'provider_sub'    => $sub,
            ];
        } catch (\Throwable $e) {
            Log::error('OAuthIdentityProvider: authentication exception', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName(): string
    {
        return 'oauth2';
    }

    /**
     * {@inheritDoc}
     *
     * Returns true when the tenant has an OAuth2 provider configured.
     *
     * @param  string  $tenantId
     * @return bool
     */
    public function supports(string $tenantId): bool
    {
        return $this->resolveProviderConfig($tenantId) !== null;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Call the OIDC UserInfo endpoint to retrieve identity claims.
     *
     * @param  array<string, string>  $config
     * @param  string                 $accessToken
     * @return array<string, mixed>|null
     */
    private function fetchUserInfo(array $config, string $accessToken): ?array
    {
        $userInfoUrl = $config['userinfo_url'] ?? '';

        if ($userInfoUrl === '') {
            return null;
        }

        try {
            $response = $this->http
                ->timeout((int) ($config['timeout_seconds'] ?? 10))
                ->withToken($accessToken)
                ->get($userInfoUrl);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve the OAuth2 provider configuration for a given tenant.
     *
     * Looks first for a tenant-specific entry, then falls back to the
     * global default (`__default__`) configuration key.
     *
     * @param  string  $tenantId
     * @return array<string, string>|null
     */
    private function resolveProviderConfig(string $tenantId): ?array
    {
        if (isset($this->tenantProviders[$tenantId])) {
            /** @var array<string, string> $cfg */
            $cfg = $this->tenantProviders[$tenantId];

            return $cfg;
        }

        if (isset($this->tenantProviders['__default__'])) {
            /** @var array<string, string> $cfg */
            $cfg = $this->tenantProviders['__default__'];

            return $cfg;
        }

        return null;
    }
}
