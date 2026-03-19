<?php

declare(strict_types=1);

namespace App\Providers\IdentityProviders;

use App\Contracts\IdentityProviderContract;
use App\DTOs\AuthResultDto;
use App\DTOs\TokenPairDto;
use App\DTOs\UserInfoDto;
use App\Exceptions\AuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Generic OAuth2 / OpenID Connect provider adapter.
 * Configured entirely through tenant runtime config — no code changes needed
 * to add a new OIDC-compliant provider.
 */
class OAuth2IdentityProvider implements IdentityProviderContract
{
    private readonly Client $http;
    private readonly string $clientId;
    private readonly string $clientSecret;
    private readonly string $tokenEndpoint;
    private readonly string $userInfoEndpoint;
    private readonly string $scope;
    private readonly string $providerName;

    public function __construct(private readonly array $config = [])
    {
        $this->clientId        = $config['client_id'] ?? '';
        $this->clientSecret    = $config['client_secret'] ?? '';
        $this->tokenEndpoint   = $config['token_endpoint'] ?? '';
        $this->userInfoEndpoint = $config['userinfo_endpoint'] ?? '';
        $this->scope           = $config['scope'] ?? 'openid profile email';
        $this->providerName    = $config['provider_name'] ?? 'oauth2';

        $this->http = new Client(['timeout' => 10.0, 'headers' => ['Accept' => 'application/json']]);
    }

    public function authenticate(array $credentials): AuthResultDto
    {
        try {
            $response = $this->http->post($this->tokenEndpoint, [
                'form_params' => [
                    'grant_type'    => 'password',
                    'username'      => $credentials['email'] ?? '',
                    'password'      => $credentials['password'] ?? '',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope'         => $this->scope,
                ],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new AuthResultDto(
                accessToken:  $data['access_token'] ?? '',
                refreshToken: $data['refresh_token'] ?? '',
                expiresIn:    (int) ($data['expires_in'] ?? 3600),
                claims:       [],
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException("OAuth2 authentication failed: {$e->getMessage()}");
        }
    }

    public function exchangeToken(string $code, string $redirectUri): TokenPairDto
    {
        try {
            $response = $this->http->post($this->tokenEndpoint, [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                    'redirect_uri'  => $redirectUri,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new TokenPairDto(
                accessToken:  $data['access_token'] ?? '',
                refreshToken: $data['refresh_token'] ?? '',
                expiresIn:    (int) ($data['expires_in'] ?? 3600),
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException("OAuth2 token exchange failed: {$e->getMessage()}");
        }
    }

    public function getUserInfo(string $accessToken): UserInfoDto
    {
        try {
            $response = $this->http->get($this->userInfoEndpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new UserInfoDto(
                externalId: $data['sub'] ?? '',
                email:      $data['email'] ?? '',
                name:       $data['name'] ?? '',
                firstName:  $data['given_name'] ?? null,
                lastName:   $data['family_name'] ?? null,
                provider:   $this->providerName,
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException("OAuth2 userinfo failed: {$e->getMessage()}");
        }
    }

    public function logout(string $accessToken): void
    {
        // Provider-specific logout handled at the application layer
    }

    public function refreshToken(string $refreshToken): TokenPairDto
    {
        try {
            $response = $this->http->post($this->tokenEndpoint, [
                'form_params' => [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new TokenPairDto(
                accessToken:  $data['access_token'] ?? '',
                refreshToken: $data['refresh_token'] ?? $refreshToken,
                expiresIn:    (int) ($data['expires_in'] ?? 3600),
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException("OAuth2 token refresh failed: {$e->getMessage()}");
        }
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function supportsSSO(): bool
    {
        return true;
    }
}
