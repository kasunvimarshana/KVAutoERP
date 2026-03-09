<?php

declare(strict_types=1);

namespace Saas\Contracts\Health;

/**
 * Contract for a single dependency health probe.
 *
 * Health checks are registered with a health-check aggregator and polled
 * periodically (or on-demand by a `/health` endpoint).  A failing critical
 * check SHOULD cause the overall service health to be reported as `unhealthy`.
 */
interface HealthCheckInterface
{
    /**
     * Executes the health probe and returns its result.
     *
     * Implementations MUST NOT throw exceptions; all errors MUST be caught
     * and returned as an unhealthy {@see HealthResultInterface}.
     *
     * @return HealthResultInterface The result of this health probe.
     */
    public function check(): HealthResultInterface;

    /**
     * Returns the unique, human-readable name of this check.
     *
     * Names are used as keys in the aggregated health response.
     * Example: `database`, `redis`, `rabbitmq`, `external-payment-gateway`.
     */
    public function getName(): string;

    /**
     * Indicates whether this check is critical to service operation.
     *
     * When `true`, a failing result MUST cause the aggregated health status to
     * be `unhealthy`.  When `false`, a failure only degrades the status to
     * `degraded`.
     */
    public function isCritical(): bool;
}
