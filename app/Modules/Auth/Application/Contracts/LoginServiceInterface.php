<?php

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Domain\Entities\AccessToken;

/**
 * Contract for the login service.
 */
interface LoginServiceInterface
{
    /**
     * Authenticate credentials and return an access token.
     *
     * @throws \Modules\Auth\Domain\Exceptions\InvalidCredentialsException
     */
    public function login(string $email, string $password): AccessToken;
}
