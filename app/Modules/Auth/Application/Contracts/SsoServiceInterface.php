<?php

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Domain\Entities\AccessToken;

/**
 * Contract for SSO (Single Sign-On) operations.
 * Passport's OAuth2 endpoints provide the OAuth2 flow;
 * this service handles the application-level SSO logic.
 */
interface SsoServiceInterface
{
    /**
     * Exchange a cross-application SSO token for a local access token.
     *
     * @param  string $ssoToken  Token issued by the SSO provider
     * @param  string $provider  SSO provider name (e.g. 'passport', 'google')
     * @return AccessToken
     *
     * @throws \Modules\Auth\Domain\Exceptions\AuthenticationException
     */
    public function exchangeToken(string $ssoToken, string $provider): AccessToken;

    /**
     * Validate an SSO token and return the user ID it represents.
     *
     * @throws \Modules\Auth\Domain\Exceptions\TokenExpiredException
     */
    public function validateSsoToken(string $ssoToken, string $provider): int;
}
