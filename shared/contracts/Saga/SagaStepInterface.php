<?php

declare(strict_types=1);

namespace Shared\Contracts\Saga;

/**
 * Saga Step Interface.
 *
 * Each step in a saga represents one service operation and its compensating action.
 */
interface SagaStepInterface
{
    /**
     * Execute the step's main action.
     *
     * @param SagaContextInterface $context Shared saga context
     * @return void
     * @throws \Throwable On failure
     */
    public function execute(SagaContextInterface $context): void;

    /**
     * Execute the compensating transaction to undo this step's effects.
     *
     * Must be idempotent - safe to call multiple times.
     *
     * @param SagaContextInterface $context Shared saga context
     * @return void
     */
    public function compensate(SagaContextInterface $context): void;

    /**
     * Get the step name for logging and state tracking.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Determine if this step can be retried on transient failure.
     *
     * @return bool
     */
    public function isRetryable(): bool;

    /**
     * Get maximum retry attempts for this step.
     *
     * @return int
     */
    public function getMaxRetries(): int;
}
