<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SuspiciousActivityServiceContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Redis-backed suspicious-activity detector.
 *
 * Tracks failed login attempts per email and per IP address.
 * After exceeding the configured threshold, the identifier is
 * temporarily blocked and a warning is logged.
 *
 * Configuration (config/auth.php):
 *   activity.max_attempts  — default 5
 *   activity.lockout_ttl   — default 900 (15 minutes)
 *   activity.attempt_ttl   — window in which attempts are counted (default 300 s)
 */
class SuspiciousActivityService implements SuspiciousActivityServiceContract
{
    private int $maxAttempts;
    private int $lockoutTtl;
    private int $attemptTtl;
    private int $ipMultiplier;

    public function __construct()
    {
        $this->maxAttempts  = (int) config('auth.activity.max_attempts', 5);
        $this->lockoutTtl   = (int) config('auth.activity.lockout_ttl',  900);
        $this->attemptTtl   = (int) config('auth.activity.attempt_ttl',  300);
        $this->ipMultiplier = (int) config('auth.activity.ip_multiplier', 2);
    }

    public function recordFailedAttempt(string $identifier, string $ipAddress): bool
    {
        $emailKey = $this->attemptKey($identifier);
        $ipKey    = $this->attemptKey($ipAddress);

        // Increment counter for both email and IP
        $emailCount = (int) Redis::incr($emailKey);
        $ipCount    = (int) Redis::incr($ipKey);

        // Set/extend TTL on the attempt counters
        Redis::expire($emailKey, $this->attemptTtl);
        Redis::expire($ipKey,    $this->attemptTtl);

        $blocked = false;

        // Block email if threshold exceeded
        if ($emailCount >= $this->maxAttempts && ! $this->isBlocked($identifier)) {
            $this->block($identifier, $this->lockoutTtl);
            $blocked = true;

            Log::warning('Suspicious activity: too many failed logins (by email)', [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
                'attempts'   => $emailCount,
            ]);
        }

        // Block IP if threshold exceeded
        if ($ipCount >= $this->maxAttempts * $this->ipMultiplier && ! $this->isBlocked($ipAddress)) {
            $this->block($ipAddress, $this->lockoutTtl * $this->ipMultiplier); // longer block for IP
            $blocked = true;

            Log::warning('Suspicious activity: too many failed logins (by IP)', [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
                'attempts'   => $ipCount,
            ]);
        }

        return $blocked || $this->isBlocked($identifier) || $this->isBlocked($ipAddress);
    }

    public function resetFailedAttempts(string $identifier): void
    {
        Redis::del($this->attemptKey($identifier));
    }

    public function isBlocked(string $identifier): bool
    {
        return (bool) Redis::exists($this->blockKey($identifier));
    }

    public function block(string $identifier, int $ttl = 3600): void
    {
        Redis::setex($this->blockKey($identifier), $ttl, '1');
    }

    public function unblock(string $identifier): void
    {
        Redis::del($this->blockKey($identifier));
        Redis::del($this->attemptKey($identifier));
    }

    public function remainingAttempts(string $identifier): int
    {
        $current = (int) (Redis::get($this->attemptKey($identifier)) ?? 0);

        return max(0, $this->maxAttempts - $current);
    }

    // ──────────────────────────────────────────────────────────
    // Redis key helpers
    // ──────────────────────────────────────────────────────────

    private function attemptKey(string $identifier): string
    {
        return 'login_attempts:' . hash('sha256', $identifier);
    }

    private function blockKey(string $identifier): string
    {
        return 'login_blocked:' . hash('sha256', $identifier);
    }
}
