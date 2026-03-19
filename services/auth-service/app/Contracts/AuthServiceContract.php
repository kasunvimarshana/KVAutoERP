<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\AuthResultDto;
use App\DTOs\TokenClaimsDto;
use App\DTOs\TokenPairDto;

interface AuthServiceContract
{
    public function login(array $credentials, string $deviceId, string $ipAddress): AuthResultDto;

    public function logout(string $accessToken, ?string $deviceId = null, bool $allDevices = false): void;

    public function refreshToken(string $refreshToken, string $deviceId): TokenPairDto;

    public function revokeToken(string $jti): void;

    public function revokeAllUserTokens(string $userId): void;

    public function verifyToken(string $accessToken): TokenClaimsDto;

    public function issueServiceToken(string $serviceId, string $serviceSecret): TokenPairDto;
}
