<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Events;

/**
 * Base contract for all domain events published to Kafka / RabbitMQ.
 */
interface BaseEventInterface
{
    public function getEventId(): string;

    public function getEventType(): string;

    public function getOccurredAt(): \DateTimeImmutable;

    public function getTenantId(): string;

    /** @return array<string, mixed> */
    public function getPayload(): array;

    /** @return array<string, mixed> */
    public function toArray(): array;
}
