<?php

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Domain\Entities\AccessToken;

/**
 * Contract for the main authentication service.
 * Handles credential validation, token issuance, and session management.
 */
interface AuthenticationServiceInterface
{
    /**
     * Authenticate user credentials and return an access token.
     *
     * @param  string $email
     * @param  string $password
     * @return AccessToken
     *
     * @throws \Modules\Auth\Domain\Exceptions\InvalidCredentialsException
     */
    public function authenticate(string $email, string $password): AccessToken;

    /**
     * Invalidate the currently authenticated user's token.
     */
    public function invalidate(int $userId): bool;
}
