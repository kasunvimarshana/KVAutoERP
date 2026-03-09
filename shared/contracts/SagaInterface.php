<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Saga Orchestration Contract.
 *
 * Defines the Saga pattern used for distributed, multi-service transactions
 * throughout the KV_SAAS platform.  Each saga consists of an ordered list of
 * {@see SagaStep} objects; failures trigger automatic compensation.
 */
interface SagaInterface
{
    /**
     * Execute the saga from start to finish.
     *
     * @param  array<string,mixed>  $context  Initial context / input data.
     * @return SagaResult                     Outcome of the saga execution.
     */
    public function execute(array $context): SagaResult;

    /**
     * Compensate (roll back) all steps that have already completed.
     *
     * @param  array<string,mixed>  $context  Context at the time of failure.
     * @param  \Throwable           $e        The exception that triggered compensation.
     * @return void
     */
    public function compensate(array $context, \Throwable $e): void;

    /**
     * Return the ordered list of steps that make up this saga.
     *
     * @return array<int, SagaStep>
     */
    public function getSteps(): array;

    /**
     * Return the unique correlation / transaction ID for this saga instance.
     *
     * @return string  UUID v4.
     */
    public function getTransactionId(): string;
}

// ─────────────────────────────────────────────────────────────────────────────
// SagaStep Contract
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Individual step within a Saga.
 */
interface SagaStep
{
    /**
     * Execute the step's forward action.
     *
     * @param  array<string,mixed>  $context  Accumulated saga context.
     * @return array<string,mixed>            Updated context to pass to the next step.
     *
     * @throws \Throwable
     */
    public function execute(array $context): array;

    /**
     * Compensate (undo) this step's effects.
     *
     * Must be idempotent.
     *
     * @param  array<string,mixed>  $context  Context at the time of compensation.
     * @return void
     */
    public function compensate(array $context): void;

    /**
     * Human-readable name for logging and observability.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Whether this step has been successfully executed.
     *
     * @return bool
     */
    public function isCompleted(): bool;

    /**
     * The result data produced by this step (null if not yet executed).
     *
     * @return array<string,mixed>|null
     */
    public function getResult(): ?array;
}

// ─────────────────────────────────────────────────────────────────────────────
// SagaResult Value Object
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Immutable value object representing the outcome of a saga execution.
 */
final readonly class SagaResult
{
    /**
     * @param  bool                   $success          Whether the saga completed without errors.
     * @param  string                 $transactionId    Correlation ID of the saga.
     * @param  array<string,mixed>    $context          Final context after all steps.
     * @param  \Throwable|null        $exception        Exception that caused failure (if any).
     * @param  array<string>          $completedSteps   Names of steps that ran successfully.
     * @param  array<string>          $compensatedSteps Names of steps that were compensated.
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly array $context,
        public readonly ?\Throwable $exception = null,
        public readonly array $completedSteps = [],
        public readonly array $compensatedSteps = [],
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(
        string $transactionId,
        array $context,
        array $completedSteps = [],
    ): static {
        return new static(
            success: true,
            transactionId: $transactionId,
            context: $context,
            completedSteps: $completedSteps,
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(
        string $transactionId,
        array $context,
        \Throwable $exception,
        array $completedSteps = [],
        array $compensatedSteps = [],
    ): static {
        return new static(
            success: false,
            transactionId: $transactionId,
            context: $context,
            exception: $exception,
            completedSteps: $completedSteps,
            compensatedSteps: $compensatedSteps,
        );
    }
}
