<?php

declare(strict_types=1);

namespace App\Shared\Saga;

use App\Shared\Contracts\SagaStep;

/**
 * Abstract Saga Step.
 *
 * Provides state tracking (completed flag, stored result) and scaffolding for
 * concrete saga steps.  Subclasses implement {@see doExecute()} and
 * {@see doCompensate()} instead of the interface methods directly, so that the
 * base class can consistently record completion state and results.
 *
 * Example:
 *   class ReserveInventoryStep extends AbstractSagaStep
 *   {
 *       protected function doExecute(array $context): array { ... }
 *       protected function doCompensate(array $context): void { ... }
 *       public function getName(): string { return 'reserve_inventory'; }
 *   }
 */
abstract class AbstractSagaStep implements SagaStep
{
    /** Whether this step's forward action completed successfully. */
    private bool $completed = false;

    /** Snapshot of the context returned by doExecute(). */
    private ?array $result = null;

    // ─────────────────────────────────────────────────────────────────────────
    // Abstract – subclasses provide the real logic
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Perform the step's business action.
     *
     * @param  array<string,mixed>  $context  Accumulated saga context.
     * @return array<string,mixed>            Updated context to pass to the next step.
     *
     * @throws \Throwable  Any exception aborts the saga and triggers compensation.
     */
    abstract protected function doExecute(array $context): array;

    /**
     * Undo the step's effects.
     *
     * Must be idempotent; may be called more than once during recovery.
     *
     * @param  array<string,mixed>  $context  Context at the time of compensation.
     * @return void
     */
    abstract protected function doCompensate(array $context): void;

    // ─────────────────────────────────────────────────────────────────────────
    // SagaStep interface – final implementations
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Delegates to {@see doExecute()}, then marks the step as completed and
     * stores the resulting context snapshot.
     */
    final public function execute(array $context): array
    {
        $updatedContext = $this->doExecute($context);

        $this->completed = true;
        $this->result    = $updatedContext;

        return $updatedContext;
    }

    /**
     * {@inheritDoc}
     *
     * Delegates to {@see doCompensate()}.  The completed flag is intentionally
     * left unchanged so that the orchestrator can still identify which steps ran.
     */
    final public function compensate(array $context): void
    {
        $this->doCompensate($context);
    }

    /**
     * {@inheritDoc}
     *
     * A machine-friendly name derived from the class short name by default.
     * Override in concrete classes to provide a more descriptive name.
     */
    public function getName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * {@inheritDoc}
     *
     * Returns true after {@see execute()} has run successfully.
     */
    final public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * {@inheritDoc}
     *
     * Returns the context snapshot produced by this step, or null if the step
     * has not yet executed.
     */
    final public function getResult(): ?array
    {
        return $this->result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers available to subclasses
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Convenience: read a value from the saga context, with a default fallback.
     *
     * @param  array<string,mixed>  $context
     * @param  string               $key
     * @param  mixed                $default
     * @return mixed
     */
    protected function fromContext(array $context, string $key, mixed $default = null): mixed
    {
        return $context[$key] ?? $default;
    }

    /**
     * Merge extra data into the context and return the updated copy.
     *
     * @param  array<string,mixed>  $context
     * @param  array<string,mixed>  $extra
     * @return array<string,mixed>
     */
    protected function mergeContext(array $context, array $extra): array
    {
        return array_merge($context, $extra);
    }
}
