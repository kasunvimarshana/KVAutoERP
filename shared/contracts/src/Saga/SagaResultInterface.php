<?php

declare(strict_types=1);

namespace Saas\Contracts\Saga;

/**
 * Encapsulates the outcome of a completed (or failed) saga execution.
 *
 * Callers inspect this object to determine whether the saga succeeded and,
 * on failure, which step failed and what compensating transactions were run.
 */
interface SagaResultInterface
{
    /**
     * Returns `true` when all saga steps completed without error.
     */
    public function isSuccessful(): bool;

    /**
     * Returns the data produced by the saga, keyed by step name or by
     * well-known result keys defined by the concrete saga.
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Returns a list of error messages collected during execution.
     *
     * An empty array indicates no errors (i.e. {@see isSuccessful()} is `true`).
     *
     * @return string[]
     */
    public function getErrors(): array;

    /**
     * Returns the names of steps that completed successfully before any failure.
     *
     * This list drives the compensation logic: only steps that appear here
     * have had their `compensate` method invoked.
     *
     * @return string[]
     */
    public function getCompletedSteps(): array;

    /**
     * Returns the name of the step that caused the saga to fail, or `null`
     * when the saga completed successfully.
     */
    public function getFailedStep(): ?string;
}
