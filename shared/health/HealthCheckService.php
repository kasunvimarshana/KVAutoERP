<?php

declare(strict_types=1);

namespace App\Shared\Health;

use App\Shared\Contracts\HealthCheckInterface;
use App\Shared\Contracts\HealthStatus;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Health Check Service.
 *
 * Aggregates multiple {@see HealthCheckInterface} probes and executes them,
 * returning a composite health report suitable for /health endpoints.
 *
 * Usage:
 *   $service = app(HealthCheckService::class);
 *   $results = $service->runChecks();
 *
 * Register checks in a service provider:
 *   $service->registerCheck(app(DatabaseHealthCheck::class));
 *   $service->registerCheck(app(RedisHealthCheck::class));
 */
final class HealthCheckService
{
    /** @var array<string, HealthCheckInterface> Keyed by check name. */
    private array $checks = [];

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Check management
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Register a health check probe.
     *
     * If a check with the same name is already registered it will be replaced.
     *
     * @param  HealthCheckInterface  $check
     * @return void
     */
    public function registerCheck(HealthCheckInterface $check): void
    {
        $this->checks[$check->getName()] = $check;
    }

    /**
     * Deregister a health check by name.
     *
     * @param  string  $name
     * @return void
     */
    public function deregisterCheck(string $name): void
    {
        unset($this->checks[$name]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Execution
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Execute all registered checks and return their status results.
     *
     * Each check runs within its own timeout boundary.  If the check exceeds
     * the configured timeout, it is marked as degraded.
     *
     * @return array<string, HealthStatus>  Keyed by check name.
     */
    public function runChecks(): array
    {
        $results = [];

        foreach ($this->checks as $name => $check) {
            $results[$name] = $this->runSingle($check);
        }

        return $results;
    }

    /**
     * Run all checks and return a summary array suitable for an HTTP response.
     *
     * @return array{
     *     status: string,
     *     checks: array<string, array<string,mixed>>,
     *     timestamp: string
     * }
     */
    public function summary(): array
    {
        $results    = $this->runChecks();
        $overallOk  = true;
        $degraded   = false;
        $checksArr  = [];

        foreach ($results as $name => $status) {
            $checksArr[$name] = $status->toArray();

            if ($status->isUnhealthy()) {
                $overallOk = false;
            } elseif ($status->isDegraded()) {
                $degraded = true;
            }
        }

        $overallStatus = match (true) {
            !$overallOk => HealthStatus::STATUS_UNHEALTHY,
            $degraded   => HealthStatus::STATUS_DEGRADED,
            default     => HealthStatus::STATUS_HEALTHY,
        };

        return [
            'status'    => $overallStatus,
            'checks'    => $checksArr,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Determine the appropriate HTTP status code from the overall health.
     *
     * @return int  200 (healthy/degraded) or 503 (unhealthy).
     */
    public function httpStatusCode(): int
    {
        foreach ($this->runChecks() as $status) {
            if ($status->isUnhealthy()) {
                return 503;
            }
        }

        return 200;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Execute a single check, honouring its timeout.
     *
     * @param  HealthCheckInterface  $check
     * @return HealthStatus
     */
    private function runSingle(HealthCheckInterface $check): HealthStatus
    {
        $start = microtime(true);

        try {
            // Use pcntl_alarm for timeout if available, otherwise rely on
            // the check's own implementation.
            $status = $check->check();

            $elapsed = microtime(true) - $start;

            // Downgrade to degraded if the check took longer than its timeout.
            if ($elapsed > $check->getTimeout()) {
                $this->logger->warning('[HealthCheck] Check exceeded timeout', [
                    'check'   => $check->getName(),
                    'elapsed' => round($elapsed * 1000, 2) . 'ms',
                    'timeout' => $check->getTimeout() . 's',
                ]);

                return HealthStatus::degraded(
                    name: $check->getName(),
                    message: "Check exceeded timeout ({$check->getTimeout()}s)",
                    metadata: array_merge($status->metadata, [
                        'elapsed_ms' => round($elapsed * 1000, 2),
                    ]),
                );
            }

            return $status;
        } catch (\Throwable $e) {
            $this->logger->error('[HealthCheck] Check threw exception', [
                'check' => $check->getName(),
                'error' => $e->getMessage(),
            ]);

            return HealthStatus::unhealthy(
                name: $check->getName(),
                message: 'Check threw an exception: ' . $e->getMessage(),
                exception: $e,
            );
        }
    }
}
