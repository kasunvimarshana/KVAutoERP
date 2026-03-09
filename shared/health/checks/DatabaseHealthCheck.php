<?php

declare(strict_types=1);

namespace App\Shared\Health\Checks;

use App\Shared\Contracts\HealthCheckInterface;
use App\Shared\Contracts\HealthStatus;
use Illuminate\Support\Facades\DB;

/**
 * Database Health Check.
 *
 * Executes a lightweight `SELECT 1` query against the default (or configured)
 * database connection and measures round-trip latency.
 */
final class DatabaseHealthCheck implements HealthCheckInterface
{
    private const int DEFAULT_TIMEOUT = 5;

    /**
     * @param  string  $connection  Laravel DB connection name to check (default: 'mysql').
     * @param  int     $timeout     Max seconds before the check is considered degraded.
     */
    public function __construct(
        private readonly string $connection = 'mysql',
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
    ) {}

    /** {@inheritDoc} */
    public function check(): HealthStatus
    {
        $start = microtime(true);

        try {
            DB::connection($this->connection)->select('SELECT 1');
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return HealthStatus::healthy(
                name: $this->getName(),
                message: 'Database connection is healthy',
                metadata: [
                    'connection'  => $this->connection,
                    'latency_ms'  => $latencyMs,
                    'driver'      => DB::connection($this->connection)->getDriverName(),
                    'database'    => DB::connection($this->connection)->getDatabaseName(),
                ],
            );
        } catch (\Throwable $e) {
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return HealthStatus::unhealthy(
                name: $this->getName(),
                message: 'Database connection failed: ' . $e->getMessage(),
                metadata: [
                    'connection' => $this->connection,
                    'latency_ms' => $latencyMs,
                ],
                exception: $e,
            );
        }
    }

    /** {@inheritDoc} */
    public function getName(): string
    {
        return 'database';
    }

    /** {@inheritDoc} */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
