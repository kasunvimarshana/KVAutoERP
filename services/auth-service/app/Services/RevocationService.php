<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RevocationServiceContract;
use Illuminate\Support\Facades\Redis;

class RevocationService implements RevocationServiceContract
{
    private const REVOKED_KEY  = 'revoked:';
    private const DEVICES_KEY  = 'devices:';

    public function revoke(string $jti, int $ttl): void
    {
        Redis::setex(self::REVOKED_KEY . $jti, $ttl + 300, '1');
    }

    public function revokeAll(string $userId): void
    {
        $devices = $this->getActiveDevices($userId);

        foreach ($devices as $device) {
            if (! empty($device['refresh_token'])) {
                Redis::del('refresh:' . $device['refresh_token']);
            }

            if (! empty($device['jti'])) {
                $this->revoke($device['jti'], (int) config('jwt.ttl', 900));
            }
        }

        Redis::del(self::DEVICES_KEY . $userId);
    }

    public function isRevoked(string $jti): bool
    {
        return (bool) Redis::exists(self::REVOKED_KEY . $jti);
    }

    public function getActiveDevices(string $userId): array
    {
        $data = Redis::get(self::DEVICES_KEY . $userId);

        return $data ? (array) json_decode((string) $data, true) : [];
    }

    public function revokeDevice(string $userId, string $deviceId): void
    {
        $devices = $this->getActiveDevices($userId);

        if (! isset($devices[$deviceId])) {
            return;
        }

        $device = $devices[$deviceId];

        if (! empty($device['refresh_token'])) {
            Redis::del('refresh:' . $device['refresh_token']);
        }

        if (! empty($device['jti'])) {
            $this->revoke($device['jti'], (int) config('jwt.ttl', 900));
        }

        unset($devices[$deviceId]);

        $ttl = (int) config('jwt.refresh_ttl', 2592000);
        Redis::setex(self::DEVICES_KEY . $userId, $ttl, json_encode($devices));
    }

    public function revokeAllDevices(string $userId): void
    {
        $this->revokeAll($userId);
    }
}
