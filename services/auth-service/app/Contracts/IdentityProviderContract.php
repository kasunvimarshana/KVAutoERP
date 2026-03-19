<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\AuthResultDto;
use App\DTOs\TokenPairDto;
use App\DTOs\UserInfoDto;

interface IdentityProviderContract
{
    public function authenticate(array $credentials): AuthResultDto;

    public function exchangeToken(string $code, string $redirectUri): TokenPairDto;

    public function getUserInfo(string $accessToken): UserInfoDto;

    public function logout(string $accessToken): void;

    public function refreshToken(string $refreshToken): TokenPairDto;

    public function getProviderName(): string;

    public function supportsSSO(): bool;
}
