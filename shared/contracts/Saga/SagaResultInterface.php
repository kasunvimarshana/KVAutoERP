<?php

declare(strict_types=1);

namespace Shared\Contracts\Saga;

/**
 * Saga Result Interface.
 *
 * Encapsulates the outcome of a saga execution.
 */
interface SagaResultInterface
{
    /**
     * Check if the saga completed successfully.
     *
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * Check if the saga failed and compensation was executed.
     *
     * @return bool
     */
    public function isCompensated(): bool;

    /**
     * Get the saga transaction identifier.
     *
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Get the final saga context data.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array;

    /**
     * Get the error that caused failure (null on success).
     *
     * @return \Throwable|null
     */
    public function getError(): ?\Throwable;

    /**
     * Get steps that were executed successfully.
     *
     * @return string[]
     */
    public function getCompletedSteps(): array;

    /**
     * Get steps that failed.
     *
     * @return string[]
     */
    public function getFailedSteps(): array;

    /**
     * Get steps that were compensated.
     *
     * @return string[]
     */
    public function getCompensatedSteps(): array;
}
