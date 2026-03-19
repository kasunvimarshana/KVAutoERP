<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\TokenRevocation;

interface TokenRevocationRepositoryInterface
{
    public function revoke(string $jti, string $userId, \DateTimeInterface $expiresAt, string $reason = 'logout'): TokenRevocation;

    public function isRevoked(string $jti): bool;

    public function revokeAllForUser(string $userId): void;

    public function cleanupExpired(): int;

    public function findByJti(string $jti): ?TokenRevocation;
}
