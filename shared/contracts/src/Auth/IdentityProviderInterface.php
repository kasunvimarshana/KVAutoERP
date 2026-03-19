<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Auth;

use KvSaas\Contracts\Auth\Dto\AuthResultDto;
use KvSaas\Contracts\Auth\Dto\TokenPairDto;
use KvSaas\Contracts\Auth\Dto\UserInfoDto;

/**
 * Contract that every IAM provider adapter must implement.
 *
 * New providers (Okta, Keycloak, Azure AD, SAML, etc.) are added by
 * implementing this interface — no core code changes required.
 * Use Strategy + Factory + Adapter patterns to resolve providers at runtime.
 */
interface IdentityProviderInterface
{
    /**
     * Authenticate using provider-specific credentials (password, SAML assertion, etc.).
     */
    public function authenticate(array $credentials): AuthResultDto;

    /**
     * Exchange an OAuth2 authorization code for a token pair.
     */
    public function exchangeToken(string $code, string $redirectUri): TokenPairDto;

    /**
     * Retrieve normalized user information from the provider.
     */
    public function getUserInfo(string $accessToken): UserInfoDto;

    /**
     * Invalidate the session at the identity provider.
     */
    public function logout(string $accessToken): void;

    /**
     * Refresh an expired access token using the provider refresh token.
     */
    public function refreshToken(string $refreshToken): TokenPairDto;

    /**
     * Return the canonical provider name (e.g. "okta", "keycloak", "azure_ad").
     */
    public function getProviderName(): string;

    /**
     * Indicate whether this provider supports SSO / federated login.
     */
    public function supportsSSO(): bool;
}
