<?php

namespace App\Application\Saga;

abstract class SagaStep
{
    /**
     * Unique step name used for tracking and logging.
     */
    abstract public function getName(): string;

    /**
     * Execute the forward action of this step.
     * Must throw an exception on failure to trigger compensation.
     */
    abstract public function execute(SagaState $state): SagaState;

    /**
     * Roll back the effects of a successfully executed step.
     */
    abstract public function compensate(SagaState $state): SagaState;

    /**
     * Override and return false for idempotent/notification steps
     * that do not require compensation.
     */
    public function canCompensate(): bool
    {
        return true;
    }

    /**
     * Convenience helper to merge a single key into the saga context.
     */
    protected function setContextValue(SagaState $state, string $key, mixed $value): SagaState
    {
        $state->setContextValue($key, $value);
        return $state;
    }
}
