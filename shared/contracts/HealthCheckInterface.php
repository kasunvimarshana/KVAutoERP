<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Health Check Contract.
 *
 * Implemented by each named health check probe (DB, Redis, broker, disk …).
 * The {@see \App\Shared\Health\HealthCheckService} aggregates all registered
 * probes and returns a composite health report.
 */
interface HealthCheckInterface
{
    /**
     * Execute the health check and return a status snapshot.
     *
     * Implementations MUST NOT throw; all errors should be caught and
     * reflected in the returned {@see HealthStatus}.
     *
     * @return HealthStatus
     */
    public function check(): HealthStatus;

    /**
     * A unique, human-readable name for this check.
     *
     * @return string  E.g. "database", "redis", "rabbitmq".
     */
    public function getName(): string;

    /**
     * Maximum seconds the check is allowed to run before being considered
     * degraded / timed-out.
     *
     * @return int  Timeout in seconds (default: 5).
     */
    public function getTimeout(): int;
}

// ─────────────────────────────────────────────────────────────────────────────
// HealthStatus Value Object
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Immutable value object capturing the result of a single health check probe.
 */
final readonly class HealthStatus
{
    /** Fully operational. */
    public const string STATUS_HEALTHY = 'healthy';

    /** Operational but with degraded performance or partial failures. */
    public const string STATUS_DEGRADED = 'degraded';

    /** Not operational. */
    public const string STATUS_UNHEALTHY = 'unhealthy';

    /**
     * @param  string              $status    One of the STATUS_* constants.
     * @param  string              $name      Name of the check that produced this result.
     * @param  string              $message   Human-readable description.
     * @param  array<string,mixed> $metadata  Additional key/value details (e.g. latency).
     * @param  \Throwable|null     $exception Exception caught during the check, if any.
     */
    public function __construct(
        public readonly string $status,
        public readonly string $name,
        public readonly string $message,
        public readonly array $metadata = [],
        public readonly ?\Throwable $exception = null,
    ) {}

    /** @return bool */
    public function isHealthy(): bool
    {
        return $this->status === self::STATUS_HEALTHY;
    }

    /** @return bool */
    public function isDegraded(): bool
    {
        return $this->status === self::STATUS_DEGRADED;
    }

    /** @return bool */
    public function isUnhealthy(): bool
    {
        return $this->status === self::STATUS_UNHEALTHY;
    }

    /**
     * Serialize to array for JSON responses.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'status'   => $this->status,
            'message'  => $this->message,
            'metadata' => $this->metadata,
        ];
    }

    // ── Factory helpers ───────────────────────────────────────────────────

    public static function healthy(
        string $name,
        string $message = 'OK',
        array $metadata = [],
    ): static {
        return new static(self::STATUS_HEALTHY, $name, $message, $metadata);
    }

    public static function degraded(
        string $name,
        string $message,
        array $metadata = [],
        ?\Throwable $exception = null,
    ): static {
        return new static(self::STATUS_DEGRADED, $name, $message, $metadata, $exception);
    }

    public static function unhealthy(
        string $name,
        string $message,
        array $metadata = [],
        ?\Throwable $exception = null,
    ): static {
        return new static(self::STATUS_UNHEALTHY, $name, $message, $metadata, $exception);
    }
}
