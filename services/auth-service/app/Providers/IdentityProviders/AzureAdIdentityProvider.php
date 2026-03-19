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
 * Azure Active Directory / Microsoft Entra ID adapter.
 * Uses the Microsoft identity platform v2.0 endpoints.
 */
class AzureAdIdentityProvider implements IdentityProviderContract
{
    private readonly Client $http;
    private readonly string $tenantId;
    private readonly string $clientId;
    private readonly string $clientSecret;
    private readonly string $tokenEndpoint;
    private readonly string $graphEndpoint;

    public function __construct(private readonly array $config = [])
    {
        $this->tenantId     = $config['azure_tenant_id'] ?? 'common';
        $this->clientId     = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';

        $this->tokenEndpoint = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        $this->graphEndpoint = 'https://graph.microsoft.com/v1.0/me';

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
                    'scope'         => 'openid profile email User.Read',
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
            throw new AuthenticationException('Azure AD authentication failed: ' . $e->getMessage());
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
                    'scope'         => 'openid profile email User.Read',
                ],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new TokenPairDto(
                accessToken:  $data['access_token'] ?? '',
                refreshToken: $data['refresh_token'] ?? '',
                expiresIn:    (int) ($data['expires_in'] ?? 3600),
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Azure AD token exchange failed: ' . $e->getMessage());
        }
    }

    public function getUserInfo(string $accessToken): UserInfoDto
    {
        try {
            $response = $this->http->get($this->graphEndpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new UserInfoDto(
                externalId: $data['id'] ?? '',
                email:      $data['mail'] ?? ($data['userPrincipalName'] ?? ''),
                name:       $data['displayName'] ?? '',
                firstName:  $data['givenName'] ?? null,
                lastName:   $data['surname'] ?? null,
                provider:   'azure_ad',
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Azure AD user info failed: ' . $e->getMessage());
        }
    }

    public function logout(string $accessToken): void
    {
        // Azure AD logout handled via front-channel logout URL
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
                    'scope'         => 'openid profile email User.Read',
                ],
            ]);

            $data = (array) json_decode((string) $response->getBody(), true);

            return new TokenPairDto(
                accessToken:  $data['access_token'] ?? '',
                refreshToken: $data['refresh_token'] ?? $refreshToken,
                expiresIn:    (int) ($data['expires_in'] ?? 3600),
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Azure AD token refresh failed: ' . $e->getMessage());
        }
    }

    public function getProviderName(): string
    {
        return 'azure_ad';
    }

    public function supportsSSO(): bool
    {
        return true;
    }
}
