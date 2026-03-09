<?php

declare(strict_types=1);

namespace App\Domain\Saga\Orchestrator;

use App\Domain\Saga\Context\SagaContext;
use App\Domain\Saga\Result\SagaResult;
use App\Domain\Order\Entities\SagaLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Saga Orchestrator.
 *
 * Coordinates distributed transactions across microservices using the Saga pattern.
 *
 * Execution Flow:
 *   1. Execute each step in sequence
 *   2. On any step failure:
 *      a. Execute compensating transactions for all completed steps in reverse order
 *      b. Mark saga as failed/compensated
 *   3. Return a SagaResult describing the outcome
 *
 * Guarantees:
 *   - At-least-once execution via retry mechanism
 *   - Full audit trail via SagaLog
 *   - Idempotent compensation steps
 */
class SagaOrchestrator
{
    /**
     * @var \App\Domain\Saga\Step\AbstractSagaStep[]
     */
    private array $steps = [];

    private string $transactionId;

    private string $state = 'pending';

    public function __construct()
    {
        $this->transactionId = (string) Str::uuid();
    }

    /**
     * Register a saga step.
     *
     * @param  \App\Domain\Saga\Step\AbstractSagaStep $step
     * @return static
     */
    public function addStep(\App\Domain\Saga\Step\AbstractSagaStep $step): static
    {
        $this->steps[] = $step;

        return $this;
    }

    /**
     * Execute the saga.
     *
     * @param  array<string, mixed> $context Initial context data
     * @return SagaResult
     */
    public function execute(array $context = []): SagaResult
    {
        $sagaContext  = new SagaContext($this->transactionId, $context);
        $completedSteps = [];
        $this->state  = 'running';

        Log::info('Saga started', [
            'transaction_id' => $this->transactionId,
            'steps'          => array_map(fn ($s) => $s->getName(), $this->steps),
        ]);

        foreach ($this->steps as $step) {
            $startTime = microtime(true);

            try {
                $this->logStep($sagaContext, $step->getName(), 'execute', 'started');

                $this->executeWithRetry($step, $sagaContext);

                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $this->logStep($sagaContext, $step->getName(), 'execute', 'completed', duration: $duration);

                $completedSteps[] = $step;

                Log::info('Saga step completed', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                    'duration_ms'    => $duration,
                ]);
            } catch (\Throwable $e) {
                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $this->logStep(
                    $sagaContext,
                    $step->getName(),
                    'execute',
                    'failed',
                    error: $e->getMessage(),
                    duration: $duration,
                );

                Log::error('Saga step failed - starting compensation', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                    'error'          => $e->getMessage(),
                ]);

                $sagaContext->markFailed($step->getName(), $e);
                $this->state = 'compensating';

                // Execute compensating transactions in reverse order
                $compensatedSteps = $this->compensate(array_reverse($completedSteps), $sagaContext);

                $this->state = 'failed';

                return new SagaResult(
                    transactionId: $this->transactionId,
                    success: false,
                    context: $sagaContext->toArray(),
                    error: $e,
                    completedSteps: array_map(fn ($s) => $s->getName(), $completedSteps),
                    failedSteps: [$step->getName()],
                    compensatedSteps: $compensatedSteps,
                );
            }
        }

        $this->state = 'completed';

        Log::info('Saga completed successfully', [
            'transaction_id' => $this->transactionId,
            'steps'          => array_map(fn ($s) => $s->getName(), $completedSteps),
        ]);

        return new SagaResult(
            transactionId: $this->transactionId,
            success: true,
            context: $sagaContext->toArray(),
            error: null,
            completedSteps: array_map(fn ($s) => $s->getName(), $completedSteps),
            failedSteps: [],
            compensatedSteps: [],
        );
    }

    /**
     * Execute a step with optional retry on transient failures.
     *
     * @param  \App\Domain\Saga\Step\AbstractSagaStep $step
     * @param  SagaContext                            $context
     * @return void
     * @throws \Throwable On non-retryable failure or exhausted retries
     */
    private function executeWithRetry(\App\Domain\Saga\Step\AbstractSagaStep $step, SagaContext $context): void
    {
        $maxAttempts = $step->isRetryable() ? ($step->getMaxRetries() + 1) : 1;
        $attempt     = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                $step->execute($context);

                return;
            } catch (\Throwable $e) {
                if ($attempt >= $maxAttempts || !$step->isRetryable()) {
                    throw $e;
                }

                Log::warning("Saga step retry {$attempt}/{$maxAttempts}", [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                    'error'          => $e->getMessage(),
                ]);

                // Exponential backoff between retries
                usleep((int) (100000 * (2 ** ($attempt - 1)))); // 100ms, 200ms, 400ms...
            }
        }
    }

    /**
     * Execute compensation steps in reverse order.
     *
     * @param  \App\Domain\Saga\Step\AbstractSagaStep[] $stepsToCompensate (already reversed)
     * @param  SagaContext                              $context
     * @return string[] Names of successfully compensated steps
     */
    private function compensate(array $stepsToCompensate, SagaContext $context): array
    {
        $compensated = [];

        foreach ($stepsToCompensate as $step) {
            try {
                $this->logStep($context, $step->getName(), 'compensate', 'started');
                $step->compensate($context);
                $this->logStep($context, $step->getName(), 'compensate', 'completed');

                $compensated[] = $step->getName();

                Log::info('Saga compensation completed', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                ]);
            } catch (\Throwable $e) {
                // Compensation failure requires manual intervention
                $this->logStep($context, $step->getName(), 'compensate', 'failed', error: $e->getMessage());

                Log::critical('Saga compensation failed - manual intervention required', [
                    'transaction_id' => $this->transactionId,
                    'step'           => $step->getName(),
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        return $compensated;
    }

    /**
     * Persist a saga step event to the audit log.
     *
     * @param  SagaContext $context
     * @param  string      $stepName
     * @param  string      $action     execute|compensate
     * @param  string      $status     started|completed|failed
     * @param  string|null $error
     * @param  int         $duration
     * @return void
     */
    private function logStep(
        SagaContext $context,
        string $stepName,
        string $action,
        string $status,
        ?string $error = null,
        int $duration = 0,
    ): void {
        try {
            SagaLog::create([
                'saga_transaction_id' => $this->transactionId,
                'order_id'            => $context->get('order_id'),
                'step_name'           => $stepName,
                'action'              => $action,
                'status'              => $status,
                'payload'             => $context->toArray(),
                'error_message'       => $error,
                'duration_ms'         => $duration,
            ]);
        } catch (\Throwable $e) {
            // Logging failure should not disrupt saga execution
            Log::error('Failed to write saga log', ['error' => $e->getMessage()]);
        }
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
