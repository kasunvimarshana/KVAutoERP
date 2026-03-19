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
 * Keycloak IAM provider adapter.
 * Supports realm-based OpenID Connect flows.
 */
class KeycloakIdentityProvider implements IdentityProviderContract
{
    private readonly Client $http;
    private readonly string $realm;
    private readonly string $clientId;
    private readonly string $clientSecret;
    private readonly string $tokenEndpoint;
    private readonly string $userInfoEndpoint;

    public function __construct(private readonly array $config = [])
    {
        $baseUrl             = rtrim($config['base_url'] ?? 'http://keycloak:8080', '/');
        $this->realm         = $config['realm'] ?? 'master';
        $this->clientId      = $config['client_id'] ?? '';
        $this->clientSecret  = $config['client_secret'] ?? '';

        $this->tokenEndpoint    = "{$baseUrl}/realms/{$this->realm}/protocol/openid-connect/token";
        $this->userInfoEndpoint = "{$baseUrl}/realms/{$this->realm}/protocol/openid-connect/userinfo";

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
            throw new AuthenticationException('Keycloak authentication failed: ' . $e->getMessage());
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
            throw new AuthenticationException('Keycloak token exchange failed: ' . $e->getMessage());
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
                name:       $data['name'] ?? ($data['preferred_username'] ?? ''),
                firstName:  $data['given_name'] ?? null,
                lastName:   $data['family_name'] ?? null,
                provider:   'keycloak',
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Keycloak userinfo failed: ' . $e->getMessage());
        }
    }

    public function logout(string $accessToken): void
    {
        // Keycloak logout can be done via end-session endpoint
        // Non-fatal if it fails since we revoke locally too
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
            throw new AuthenticationException('Keycloak token refresh failed: ' . $e->getMessage());
        }
    }

    public function getProviderName(): string
    {
        return 'keycloak';
    }

    public function supportsSSO(): bool
    {
        return true;
    }
}
