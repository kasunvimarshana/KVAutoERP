<?php

declare(strict_types=1);

namespace Saas\Contracts\Saga;

/**
 * Defines the contract for a saga orchestrator.
 *
 * A saga coordinates a sequence of local transactions across multiple services.
 * When any step fails the orchestrator invokes the compensating transactions of
 * all previously completed, compensable steps in reverse order to maintain
 * eventual consistency.
 */
interface SagaInterface
{
    /**
     * Runs all saga steps in sequence.
     *
     * The orchestrator MUST catch any exception thrown by a step, trigger
     * compensation for all completed compensable steps, and return a
     * {@see SagaResultInterface} that accurately reflects the failure.
     *
     * @param array<string, mixed> $context Initial data passed to the first step.
     *
     * @return SagaResultInterface The outcome of the saga execution.
     */
    public function execute(array $context): SagaResultInterface;

    /**
     * Runs compensation logic for previously completed steps.
     *
     * Implementations MUST iterate the completed steps in reverse order,
     * calling {@see SagaStepInterface::compensate()} on each compensable step.
     * This method SHOULD NOT throw; exceptions during compensation MUST be
     * caught, logged, and the process continued for all remaining steps.
     *
     * @param array<string, mixed> $context Shared context at the point of failure.
     * @param \Throwable           $exception The original exception that triggered compensation.
     */
    public function compensate(array $context, \Throwable $exception): void;

    /**
     * Returns the ordered list of steps that make up this saga.
     *
     * Steps are executed in the order returned and compensated in reverse order.
     *
     * @return SagaStepInterface[]
     */
    public function getSteps(): array;
}
