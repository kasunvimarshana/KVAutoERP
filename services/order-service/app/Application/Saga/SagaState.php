<?php

namespace App\Application\Saga;

class SagaState
{
    const STATUS_STARTED      = 'started';
    const STATUS_RUNNING      = 'running';
    const STATUS_COMPLETED    = 'completed';
    const STATUS_COMPENSATING = 'compensating';
    const STATUS_COMPENSATED  = 'compensated';
    const STATUS_FAILED       = 'failed';

    private array   $completedSteps   = [];
    private array   $compensatedSteps = [];
    private array   $context          = [];
    private string  $status           = self::STATUS_STARTED;
    private ?string $failureReason    = null;
    private array   $events           = [];

    public function __construct(
        private readonly string $sagaId,
        private readonly array  $payload
    ) {}

    // -------------------------------------------------------------------------
    // Step tracking
    // -------------------------------------------------------------------------

    public function markStepCompleted(string $stepName): void
    {
        if (!in_array($stepName, $this->completedSteps, true)) {
            $this->completedSteps[] = $stepName;
        }
        $this->status = self::STATUS_RUNNING;
        $this->addEvent('step_completed', ['step' => $stepName]);
    }

    public function markStepCompensated(string $stepName): void
    {
        if (!in_array($stepName, $this->compensatedSteps, true)) {
            $this->compensatedSteps[] = $stepName;
        }
        $this->addEvent('step_compensated', ['step' => $stepName]);
    }

    // -------------------------------------------------------------------------
    // Context management
    // -------------------------------------------------------------------------

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContextValue(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    public function getContextValue(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    // -------------------------------------------------------------------------
    // Event log
    // -------------------------------------------------------------------------

    public function addEvent(string $type, array $data = []): void
    {
        $this->events[] = [
            'type'       => $type,
            'data'       => $data,
            'occurred_at'=> now()->toIso8601String(),
        ];
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    // -------------------------------------------------------------------------
    // Status transitions
    // -------------------------------------------------------------------------

    public function markFailed(string $reason): void
    {
        $this->status        = self::STATUS_FAILED;
        $this->failureReason = $reason;
        $this->addEvent('saga_failed', ['reason' => $reason]);
    }

    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->addEvent('saga_completed');
    }

    public function markCompensating(): void
    {
        $this->status = self::STATUS_COMPENSATING;
        $this->addEvent('saga_compensating');
    }

    public function markCompensated(): void
    {
        $this->status = self::STATUS_COMPENSATED;
        $this->addEvent('saga_compensated');
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function getCompletedSteps(): array
    {
        return $this->completedSteps;
    }

    public function getCompensatedSteps(): array
    {
        return $this->compensatedSteps;
    }

    public function getSagaId(): string
    {
        return $this->sagaId;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCompensated(): bool
    {
        return $this->status === self::STATUS_COMPENSATED;
    }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------

    public function toArray(): array
    {
        return [
            'saga_id'           => $this->sagaId,
            'status'            => $this->status,
            'payload'           => $this->payload,
            'context'           => $this->context,
            'completed_steps'   => $this->completedSteps,
            'compensated_steps' => $this->compensatedSteps,
            'events'            => $this->events,
            'failure_reason'    => $this->failureReason,
        ];
    }
}
