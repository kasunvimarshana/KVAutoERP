<?php

declare(strict_types=1);

namespace App\Shared\Health\Checks;

use App\Shared\Contracts\HealthCheckInterface;
use App\Shared\Contracts\HealthStatus;
use Illuminate\Support\Facades\Redis;

/**
 * Redis Health Check.
 *
 * Sends a PING command to Redis and measures round-trip latency.
 * Also captures the Redis server version and used memory.
 */
final class RedisHealthCheck implements HealthCheckInterface
{
    private const int DEFAULT_TIMEOUT = 5;

    /**
     * @param  string  $connection  Laravel Redis connection name (default: 'default').
     * @param  int     $timeout     Max seconds before the check is considered degraded.
     */
    public function __construct(
        private readonly string $connection = 'default',
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
    ) {}

    /** {@inheritDoc} */
    public function check(): HealthStatus
    {
        $start = microtime(true);

        try {
            $redis     = Redis::connection($this->connection);
            $pong      = $redis->ping();
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            if (strtoupper((string) $pong) !== 'PONG' && $pong !== 1 && $pong !== true) {
                return HealthStatus::unhealthy(
                    name: $this->getName(),
                    message: 'Redis PING returned unexpected response: ' . (string) $pong,
                    metadata: ['latency_ms' => $latencyMs],
                );
            }

            // Collect extra metadata without failing if INFO is unavailable.
            $metadata = ['connection' => $this->connection, 'latency_ms' => $latencyMs];

            try {
                $info = $redis->info('server');
                $metadata['redis_version'] = $info['redis_version'] ?? $info['Server']['redis_version'] ?? 'unknown';

                $memInfo = $redis->info('memory');
                $usedMemoryHuman = $memInfo['used_memory_human']
                    ?? $memInfo['Memory']['used_memory_human']
                    ?? 'unknown';
                $metadata['used_memory'] = $usedMemoryHuman;
            } catch (\Throwable) {
                // Non-fatal – metadata is best-effort.
            }

            return HealthStatus::healthy(
                name: $this->getName(),
                message: 'Redis connection is healthy',
                metadata: $metadata,
            );
        } catch (\Throwable $e) {
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return HealthStatus::unhealthy(
                name: $this->getName(),
                message: 'Redis connection failed: ' . $e->getMessage(),
                metadata: ['connection' => $this->connection, 'latency_ms' => $latencyMs],
                exception: $e,
            );
        }
    }

    /** {@inheritDoc} */
    public function getName(): string
    {
        return 'redis';
    }

    /** {@inheritDoc} */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
