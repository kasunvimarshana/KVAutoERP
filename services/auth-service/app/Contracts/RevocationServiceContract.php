<?php

declare(strict_types=1);

namespace App\Contracts;

interface RevocationServiceContract
{
    public function revoke(string $jti, int $ttl): void;

    public function revokeAll(string $userId): void;

    public function isRevoked(string $jti): bool;

    public function getActiveDevices(string $userId): array;

    public function revokeDevice(string $userId, string $deviceId): void;

    public function revokeAllDevices(string $userId): void;
}
