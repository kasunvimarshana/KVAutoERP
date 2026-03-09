<?php

declare(strict_types=1);

namespace App\Shared\Health\Checks;

use App\Shared\Contracts\HealthCheckInterface;
use App\Shared\Contracts\HealthStatus;
use App\Shared\Contracts\MessageBrokerInterface;

/**
 * Message Broker Health Check.
 *
 * Delegates to {@see MessageBrokerInterface::getConnectionStatus()} to probe
 * the configured broker (RabbitMQ or Kafka) and returns an appropriate
 * {@see HealthStatus} based on the result.
 */
final class MessageBrokerHealthCheck implements HealthCheckInterface
{
    private const int DEFAULT_TIMEOUT = 10;

    /**
     * @param  MessageBrokerInterface  $broker   The broker to probe.
     * @param  int                     $timeout  Max seconds before check is degraded.
     */
    public function __construct(
        private readonly MessageBrokerInterface $broker,
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
    ) {}

    /** {@inheritDoc} */
    public function check(): HealthStatus
    {
        $start = microtime(true);

        try {
            $status    = $this->broker->getConnectionStatus();
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            $status['latency_ms'] = $latencyMs;

            if (!($status['connected'] ?? false)) {
                return HealthStatus::unhealthy(
                    name: $this->getName(),
                    message: 'Message broker is not connected',
                    metadata: $status,
                );
            }

            // Mark as degraded if latency is high (> 500 ms).
            if ($latencyMs > 500) {
                return HealthStatus::degraded(
                    name: $this->getName(),
                    message: "Message broker is slow (latency: {$latencyMs}ms)",
                    metadata: $status,
                );
            }

            return HealthStatus::healthy(
                name: $this->getName(),
                message: 'Message broker connection is healthy',
                metadata: $status,
            );
        } catch (\Throwable $e) {
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return HealthStatus::unhealthy(
                name: $this->getName(),
                message: 'Message broker check threw an exception: ' . $e->getMessage(),
                metadata: ['latency_ms' => $latencyMs],
                exception: $e,
            );
        }
    }

    /** {@inheritDoc} */
    public function getName(): string
    {
        return 'message_broker';
    }

    /** {@inheritDoc} */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
