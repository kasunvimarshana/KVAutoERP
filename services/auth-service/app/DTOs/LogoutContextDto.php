<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class LogoutContextDto
{
    public function __construct(
        public string $userId,
        public string $tenantId,
        public string $sessionId,
        public string $accessTokenJti,
        public int $accessTokenRemainingTtlSeconds,
        public string $deviceId,
        public string $ipAddress = '',
    ) {}
}
