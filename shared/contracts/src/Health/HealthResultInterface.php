<?php

declare(strict_types=1);

namespace Saas\Contracts\Health;

/**
 * Represents the result of a single health check probe.
 *
 * Consumers such as `/health` endpoints aggregate multiple
 * {@see HealthResultInterface} objects to determine the overall service health.
 */
interface HealthResultInterface
{
    /** Status constant indicating the dependency is operating normally. */
    public const STATUS_HEALTHY = 'healthy';

    /** Status constant indicating the dependency is operating with degraded performance. */
    public const STATUS_DEGRADED = 'degraded';

    /** Status constant indicating the dependency is unavailable. */
    public const STATUS_UNHEALTHY = 'unhealthy';

    /**
     * Returns `true` when the check passed without errors (status is `healthy`).
     */
    public function isHealthy(): bool;

    /**
     * Returns the normalised status string.
     *
     * @return string One of: `healthy`, `degraded`, `unhealthy`.
     */
    public function getStatus(): string;

    /**
     * Returns a human-readable message describing the result, especially useful
     * when the check is degraded or unhealthy.
     */
    public function getMessage(): string;

    /**
     * Returns additional diagnostic data (e.g. response times, version strings,
     * connection counts) as a key-value map.
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Returns the timestamp at which this check was performed.
     */
    public function getCheckedAt(): \DateTimeImmutable;
}
