<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Auth;

use KvSaas\Contracts\Auth\Dto\AuthResultDto;
use KvSaas\Contracts\Auth\Dto\TokenClaimsDto;
use KvSaas\Contracts\Auth\Dto\TokenPairDto;

/**
 * Contract for the centralized Auth microservice.
 *
 * All other microservices interact with Auth exclusively through this interface,
 * ensuring loose coupling and independent deployability.
 */
interface AuthServiceInterface
{
    /**
     * Authenticate a user and issue a token pair.
     *
     * @param  array{email?: string, password?: string, provider?: string, tenant_id?: string, code?: string}  $credentials
     */
    public function login(array $credentials, string $deviceId, string $ipAddress): AuthResultDto;

    /**
     * Revoke the provided access token (and optionally all device tokens).
     */
    public function logout(string $accessToken, ?string $deviceId = null, bool $allDevices = false): void;

    /**
     * Rotate the refresh token and issue a new access + refresh pair.
     */
    public function refreshToken(string $refreshToken, string $deviceId): TokenPairDto;

    /**
     * Blacklist a single token by its JTI claim.
     */
    public function revokeToken(string $jti): void;

    /**
     * Invalidate all tokens for the given user (global logout).
     */
    public function revokeAllUserTokens(string $userId): void;

    /**
     * Verify an access token and return its decoded claims.
     *
     * @throws \RuntimeException when the token is invalid or revoked
     */
    public function verifyToken(string $accessToken): TokenClaimsDto;

    /**
     * Issue a short-lived service-to-service token.
     */
    public function issueServiceToken(string $serviceId, string $serviceSecret): TokenPairDto;
}
