<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Domain\Entities\User;

interface AuthServiceInterface
{
    /**
     * Validate credentials and return the authenticated user.
     */
    public function login(string $email, string $password, ?int $tenantId = null): User;

    /**
     * Revoke the current session / token for the given user.
     */
    public function logout(int $userId): void;

    /**
     * Return a refreshed token payload for the given user (framework-agnostic).
     * Returns an array with at minimum ['token', 'expires_at'].
     *
     * @return array<string, mixed>
     */
    public function refresh(int $userId): array;
}
