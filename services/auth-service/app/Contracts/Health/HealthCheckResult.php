<?php

declare(strict_types=1);

namespace App\Contracts\Health;

final class HealthCheckResult
{
    private function __construct(
        public readonly string $name,
        public readonly bool $healthy,
        public readonly string $message,
        public readonly array $context = [],
        public readonly ?float $responseTimeMs = null,
    ) {}

    public static function healthy(
        string $name,
        string $message = 'OK',
        array $context = [],
        ?float $responseTimeMs = null,
    ): self {
        return new self($name, true, $message, $context, $responseTimeMs);
    }

    public static function unhealthy(
        string $name,
        string $message,
        array $context = [],
        ?float $responseTimeMs = null,
    ): self {
        return new self($name, false, $message, $context, $responseTimeMs);
    }

    public function toArray(): array
    {
        return [
            'name'            => $this->name,
            'status'          => $this->healthy ? 'healthy' : 'unhealthy',
            'message'         => $this->message,
            'response_time_ms' => $this->responseTimeMs,
            'context'         => $this->context,
        ];
    }
}
