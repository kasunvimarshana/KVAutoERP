<?php

declare(strict_types=1);

namespace App\Contracts;

interface TokenServiceContract
{
    public function issue(array $claims, int $ttl): string;

    public function issueRefreshToken(string $userId, string $deviceId, string $jti): string;

    public function verify(string $token): array;

    public function decode(string $token, bool $verify = true): array;

    public function revoke(string $jti): void;

    public function isRevoked(string $jti): bool;

    public function getPublicKey(): string;

    /** Return the public key as a JWKS (JSON Web Key Set) array. */
    public function getJwks(): array;

    public function buildClaims(array $user, string $deviceId, string $tenantId): array;
}
