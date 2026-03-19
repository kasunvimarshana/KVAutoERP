<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Contracts;

interface JwtVerifierInterface
{
    /**
     * Decode and verify a JWT access token using the Auth service's public key.
     * Performs local verification — no round-trip to the Auth service.
     *
     * @param  string  $token  Raw Bearer token
     * @return array   Decoded payload
     *
     * @throws \KvEnterprise\SharedAuth\Exceptions\TokenVerificationException
     */
    public function verify(string $token): array;

    /**
     * Check whether the given JTI has been revoked.
     */
    public function isRevoked(string $jti): bool;

    /**
     * Return the token's remaining TTL in seconds (0 if expired).
     */
    public function getRemainingTtl(array $payload): int;
}
