<?php

declare(strict_types=1);

namespace Saas\Contracts\Saga;

/**
 * Represents a single, atomic step within a saga.
 *
 * Each step performs a local transaction via {@see execute()} and, if the step
 * is compensable, can undo that transaction via {@see compensate()}.
 */
interface SagaStepInterface
{
    /**
     * Executes the step's local transaction.
     *
     * The step SHOULD add any data it produces to the returned context so that
     * subsequent steps can consume it.  Implementations MUST throw an exception
     * on failure; returning normally signals success.
     *
     * @param array<string, mixed> $context Shared saga context from previous steps.
     *
     * @return array<string, mixed> Updated context to be passed to the next step.
     *
     * @throws \Throwable When the step cannot complete successfully.
     */
    public function execute(array $context): array;

    /**
     * Performs the compensating (undo) transaction for this step.
     *
     * This method is invoked by the saga orchestrator when a later step fails
     * and the saga needs to roll back.  Implementations MUST be idempotent.
     *
     * @param array<string, mixed> $context Shared saga context at the point of failure.
     */
    public function compensate(array $context): void;

    /**
     * Returns the unique name of this step within the saga.
     *
     * Names are used for logging, tracing, and determining which compensation
     * actions have already been executed.
     */
    public function getName(): string;

    /**
     * Indicates whether this step supports compensation.
     *
     * Steps that produce side-effects that cannot be undone (e.g. sending an
     * e-mail) should return `false`; the orchestrator will skip calling
     * {@see compensate()} for non-compensable steps.
     */
    public function isCompensable(): bool;
}
