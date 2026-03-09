<?php

declare(strict_types=1);

namespace Shared\Contracts\Saga;

/**
 * Saga Orchestrator Interface.
 *
 * Defines the contract for saga orchestrators that coordinate
 * distributed transactions across multiple services with compensating rollbacks.
 */
interface SagaOrchestratorInterface
{
    /**
     * Execute the saga with the given context data.
     *
     * Runs each step in sequence. On failure, executes compensating
     * transactions in reverse order to maintain consistency.
     *
     * @param array<string, mixed> $context Initial saga context data
     * @return SagaResultInterface
     */
    public function execute(array $context): SagaResultInterface;

    /**
     * Register a saga step.
     *
     * @param SagaStepInterface $step
     * @return static
     */
    public function addStep(SagaStepInterface $step): static;

    /**
     * Get the unique saga transaction identifier.
     *
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Get the current saga state.
     *
     * @return string One of: pending, running, completed, compensating, failed
     */
    public function getState(): string;
}
