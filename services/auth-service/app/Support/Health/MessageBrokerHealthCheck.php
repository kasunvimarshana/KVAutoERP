<?php

declare(strict_types=1);

namespace App\Support\Health;

use App\Contracts\Health\HealthCheckInterface;
use App\Contracts\Health\HealthCheckResult;
use App\Contracts\Messaging\MessageBrokerInterface;
use Throwable;

class MessageBrokerHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private readonly MessageBrokerInterface $broker,
    ) {}

    public function name(): string
    {
        return 'message_broker';
    }

    public function check(): HealthCheckResult
    {
        $start = microtime(true);

        try {
            $connected  = $this->broker->isConnected();
            $responseMs = (microtime(true) - $start) * 1000;

            if (! $connected) {
                return HealthCheckResult::unhealthy(
                    $this->name(),
                    'Message broker connection is not established.',
                    [],
                    round($responseMs, 2),
                );
            }

            return HealthCheckResult::healthy(
                $this->name(),
                'Message broker is connected.',
                [],
                round($responseMs, 2),
            );
        } catch (Throwable $e) {
            $responseMs = (microtime(true) - $start) * 1000;

            return HealthCheckResult::unhealthy(
                $this->name(),
                "Message broker check failed: {$e->getMessage()}",
                ['exception' => $e::class],
                round($responseMs, 2),
            );
        }
    }
}
