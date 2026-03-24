<?php

namespace Modules\Auth\Application\Contracts;

/**
 * Contract for the logout service.
 */
interface LogoutServiceInterface
{
    /**
     * Revoke the current user's access token.
     */
    public function logout(int $userId): bool;
}
