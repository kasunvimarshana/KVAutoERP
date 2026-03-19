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
 * Okta IAM provider adapter.
 * Implements OAuth2/OIDC flows against the Okta authorization server.
 */
class OktaIdentityProvider implements IdentityProviderContract
{
    private readonly Client $http;
    private readonly string $domain;
    private readonly string $clientId;
    private readonly string $clientSecret;

    public function __construct(private readonly array $config = [])
    {
        $this->domain       = $config['domain'] ?? '';
        $this->clientId     = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';

        $this->http = new Client([
            'base_uri' => "https://{$this->domain}",
            'timeout'  => 10.0,
            'headers'  => ['Accept' => 'application/json'],
        ]);
    }

    public function authenticate(array $credentials): AuthResultDto
    {
        try {
            $response = $this->http->post('/oauth2/v1/token', [
                'form_params' => [
                    'grant_type'    => 'password',
                    'username'      => $credentials['email'] ?? '',
                    'password'      => $credentials['password'] ?? '',
                    'scope'         => 'openid profile email',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
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
            throw new AuthenticationException('Okta authentication failed: ' . $e->getMessage());
        }
    }

    public function exchangeToken(string $code, string $redirectUri): TokenPairDto
    {
        try {
            $response = $this->http->post('/oauth2/v1/token', [
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
            throw new AuthenticationException('Okta token exchange failed: ' . $e->getMessage());
        }
    }

    public function getUserInfo(string $accessToken): UserInfoDto
    {
        try {
            $response = $this->http->get('/oauth2/v1/userinfo', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new UserInfoDto(
                externalId: $data['sub'] ?? '',
                email:      $data['email'] ?? '',
                name:       $data['name'] ?? '',
                firstName:  $data['given_name'] ?? null,
                lastName:   $data['family_name'] ?? null,
                provider:   'okta',
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Okta userinfo failed: ' . $e->getMessage());
        }
    }

    public function logout(string $accessToken): void
    {
        try {
            $this->http->get('/oauth2/v1/logout', [
                'query' => ['id_token_hint' => $accessToken],
            ]);
        } catch (GuzzleException) {
            // Non-fatal: log and continue
        }
    }

    public function refreshToken(string $refreshToken): TokenPairDto
    {
        try {
            $response = $this->http->post('/oauth2/v1/token', [
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
            throw new AuthenticationException('Okta token refresh failed: ' . $e->getMessage());
        }
    }

    public function getProviderName(): string
    {
        return 'okta';
    }

    public function supportsSSO(): bool
    {
        return true;
    }
}
