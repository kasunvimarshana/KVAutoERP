<?php

declare(strict_types=1);

namespace App\Domain\Saga\Result;

/**
 * Saga Result.
 *
 * Immutable value object representing the outcome of a saga execution.
 */
final class SagaResult
{
    /**
     * @param string         $transactionId
     * @param bool           $success
     * @param array<string, mixed> $context
     * @param \Throwable|null $error
     * @param string[]       $completedSteps
     * @param string[]       $failedSteps
     * @param string[]       $compensatedSteps
     */
    public function __construct(
        private readonly string $transactionId,
        private readonly bool $success,
        private readonly array $context,
        private readonly ?\Throwable $error,
        private readonly array $completedSteps,
        private readonly array $failedSteps,
        private readonly array $compensatedSteps,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isCompensated(): bool
    {
        return !$this->success && !empty($this->compensatedSteps);
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /** @return array<string, mixed> */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getError(): ?\Throwable
    {
        return $this->error;
    }

    /** @return string[] */
    public function getCompletedSteps(): array
    {
        return $this->completedSteps;
    }

    /** @return string[] */
    public function getFailedSteps(): array
    {
        return $this->failedSteps;
    }

    /** @return string[] */
    public function getCompensatedSteps(): array
    {
        return $this->compensatedSteps;
    }
}
