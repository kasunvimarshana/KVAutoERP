<?php

declare(strict_types=1);

namespace App\Domain\Saga\Step;

use App\Domain\Saga\Context\SagaContext;

/**
 * Abstract Saga Step.
 *
 * Base class for all saga steps. Subclasses implement execute() and compensate().
 * Each step represents one service operation in the distributed transaction.
 */
abstract class AbstractSagaStep
{
    /**
     * Execute this step's main action.
     *
     * @param  SagaContext $context
     * @return void
     * @throws \Throwable On failure
     */
    abstract public function execute(SagaContext $context): void;

    /**
     * Execute the compensating transaction to undo this step's effects.
     *
     * Must be idempotent - safe to call multiple times.
     *
     * @param  SagaContext $context
     * @return void
     */
    abstract public function compensate(SagaContext $context): void;

    /**
     * Get the step name for logging and state tracking.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Whether this step can be retried on transient failure.
     *
     * @return bool
     */
    public function isRetryable(): bool
    {
        return true;
    }

    /**
     * Maximum retry attempts.
     *
     * @return int
     */
    public function getMaxRetries(): int
    {
        return 3;
    }
}
