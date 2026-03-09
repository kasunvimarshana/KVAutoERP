<?php

declare(strict_types=1);

namespace App\Shared\Saga;

use App\Shared\Contracts\SagaInterface;
use App\Shared\Contracts\SagaResult;
use App\Shared\Contracts\SagaStep;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Saga State Enum.
 *
 * Tracks the lifecycle of a saga instance for observability and recovery.
 */
enum SagaState: string
{
    case PENDING      = 'pending';
    case RUNNING      = 'running';
    case COMPLETED    = 'completed';
    case COMPENSATING = 'compensating';
    case FAILED       = 'failed';
}

/**
 * Abstract Saga Orchestrator.
 *
 * Concrete sagas extend this class and implement {@see defineSteps()} to
 * return the ordered list of {@see SagaStep} objects.  The orchestrator
 * handles forward execution, failure detection, and reverse compensation
 * in a thread-safe manner using a per-instance mutex flag.
 *
 * Usage:
 *   class CreateOrderSaga extends SagaOrchestrator { ... }
 *   $result = $saga->execute($context);
 */
abstract class SagaOrchestrator implements SagaInterface
{
    /** Unique correlation ID for this saga instance (UUID v4). */
    private readonly string $transactionId;

    /** Current lifecycle state. */
    private SagaState $state = SagaState::PENDING;

    /** Steps that have successfully executed (in order). */
    private array $completedSteps = [];

    /** Steps that have been compensated (in order, for logging). */
    private array $compensatedSteps = [];

    /** Guard against concurrent executions of the same instance. */
    private bool $executing = false;

    /** Steps registered by {@see defineSteps()}. */
    private array $steps = [];

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        $this->transactionId = (string) Str::uuid();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Abstract API – subclasses define their steps here
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return the ordered list of SagaStep objects for this saga.
     *
     * Called once per {@see execute()} invocation.
     *
     * @return array<int, SagaStep>
     */
    abstract protected function defineSteps(): array;

    // ─────────────────────────────────────────────────────────────────────────
    // SagaInterface implementation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Runs each step sequentially, accumulating context updates.
     * On any exception, triggers {@see compensate()} and returns a failure result.
     */
    final public function execute(array $context): SagaResult
    {
        if ($this->executing) {
            throw new \RuntimeException(
                "Saga [{$this->transactionId}] is already executing. "
                . 'Create a new instance for concurrent sagas.'
            );
        }

        $this->executing      = true;
        $this->completedSteps = [];
        $this->steps          = $this->defineSteps();
        $this->setState(SagaState::RUNNING);

        $this->logger->info('[Saga] Starting', [
            'saga'           => static::class,
            'transaction_id' => $this->transactionId,
            'steps'          => array_map(fn (SagaStep $s) => $s->getName(), $this->steps),
        ]);

        try {
            foreach ($this->steps as $step) {
                $this->logger->debug('[Saga] Executing step', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                ]);

                $context = $step->execute($context);
                $this->completedSteps[] = $step;

                $this->logger->debug('[Saga] Step completed', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                ]);
            }

            $this->setState(SagaState::COMPLETED);
            $this->executing = false;

            $this->logger->info('[Saga] Completed successfully', [
                'transaction_id' => $this->transactionId,
            ]);

            return SagaResult::success(
                transactionId: $this->transactionId,
                context: $context,
                completedSteps: array_map(
                    fn (SagaStep $s) => $s->getName(),
                    $this->completedSteps,
                ),
            );
        } catch (\Throwable $e) {
            $this->logger->error('[Saga] Step failed, starting compensation', [
                'transaction_id' => $this->transactionId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);

            $this->compensate($context, $e);
            $this->executing = false;

            return SagaResult::failure(
                transactionId: $this->transactionId,
                context: $context,
                exception: $e,
                completedSteps: array_map(
                    fn (SagaStep $s) => $s->getName(),
                    $this->completedSteps,
                ),
                compensatedSteps: $this->compensatedSteps,
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * Executes compensate() on each completed step in LIFO order.
     * Compensation errors are logged but do not stop the rollback chain.
     */
    final public function compensate(array $context, \Throwable $e): void
    {
        $this->setState(SagaState::COMPENSATING);

        $this->logger->warning('[Saga] Compensating', [
            'transaction_id'  => $this->transactionId,
            'steps_to_revert' => array_map(fn (SagaStep $s) => $s->getName(), $this->completedSteps),
        ]);

        // Reverse order – LIFO compensation
        $stepsToCompensate = array_reverse($this->completedSteps);

        foreach ($stepsToCompensate as $step) {
            try {
                $this->logger->debug('[Saga] Compensating step', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                ]);

                $step->compensate($context);
                $this->compensatedSteps[] = $step->getName();

                $this->logger->debug('[Saga] Step compensated', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                ]);
            } catch (\Throwable $compensationError) {
                // Log but continue rolling back remaining steps
                $this->logger->critical('[Saga] Compensation step failed', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                    'error'          => $compensationError->getMessage(),
                ]);
            }
        }

        $this->setState(SagaState::FAILED);

        $this->logger->warning('[Saga] Compensation complete', [
            'transaction_id'    => $this->transactionId,
            'compensated_steps' => $this->compensatedSteps,
        ]);
    }

    /** {@inheritDoc} */
    final public function getSteps(): array
    {
        return $this->steps;
    }

    /** {@inheritDoc} */
    final public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────────────────

    /** @return SagaState */
    public function getState(): SagaState
    {
        return $this->state;
    }

    /** @return array<string> Names of completed steps. */
    public function getCompletedStepNames(): array
    {
        return array_map(fn (SagaStep $s) => $s->getName(), $this->completedSteps);
    }

    /** @return array<string> Names of compensated steps. */
    public function getCompensatedStepNames(): array
    {
        return $this->compensatedSteps;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function setState(SagaState $state): void
    {
        $this->state = $state;

        $this->logger->debug('[Saga] State changed', [
            'transaction_id' => $this->transactionId,
            'state'          => $state->value,
        ]);
    }
}
