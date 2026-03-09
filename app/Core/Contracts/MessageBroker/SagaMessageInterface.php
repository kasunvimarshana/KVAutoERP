<?php

declare(strict_types=1);

namespace App\Core\Contracts\MessageBroker;

/**
 * Saga Message Contract.
 *
 * Each step in a Saga must be able to produce compensating commands
 * to enable distributed rollback via the message broker.
 */
interface SagaMessageInterface
{
    /** @return string Unique correlation ID that tracks the whole Saga */
    public function getCorrelationId(): string;

    /** @return string The saga step name */
    public function getStepName(): string;

    /** @return array<string,mixed> Message payload */
    public function getPayload(): array;

    /** @return string Topic/exchange to publish this message to */
    public function getTopic(): string;
}
