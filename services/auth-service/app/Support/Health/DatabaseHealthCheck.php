<?php

declare(strict_types=1);

namespace App\Support\Health;

use App\Contracts\Health\HealthCheckInterface;
use App\Contracts\Health\HealthCheckResult;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private readonly string $connection = 'mysql',
    ) {}

    public function name(): string
    {
        return "database:{$this->connection}";
    }

    public function check(): HealthCheckResult
    {
        $start = microtime(true);

        try {
            DB::connection($this->connection)->getPdo();

            $result = DB::connection($this->connection)
                ->selectOne('SELECT 1 AS alive');

            $responseMs = (microtime(true) - $start) * 1000;

            return HealthCheckResult::healthy(
                $this->name(),
                'Database connection is healthy.',
                ['connection' => $this->connection, 'alive' => (bool) $result?->alive],
                round($responseMs, 2),
            );
        } catch (Throwable $e) {
            $responseMs = (microtime(true) - $start) * 1000;

            return HealthCheckResult::unhealthy(
                $this->name(),
                "Database connection failed: {$e->getMessage()}",
                ['connection' => $this->connection, 'exception' => $e::class],
                round($responseMs, 2),
            );
        }
    }
}
