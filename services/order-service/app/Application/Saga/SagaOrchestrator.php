<?php

namespace App\Application\Saga;

use App\Domain\Saga\Entities\SagaStateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SagaOrchestrator
{
    /** @var SagaStep[] */
    private array $steps = [];

    public function addStep(SagaStep $step): static
    {
        $this->steps[] = $step;
        return $this;
    }

    /**
     * Execute all registered steps in order.
     * On failure, compensate all successfully executed steps in reverse order.
     */
    public function execute(array $payload): SagaState
    {
        $sagaId = Str::uuid()->toString();
        $state  = new SagaState($sagaId, $payload);

        $this->persistSagaState($state, SagaState::STATUS_STARTED);

        /** @var SagaStep[] $executedSteps */
        $executedSteps = [];

        try {
            foreach ($this->steps as $step) {
                Log::info("Saga [{$sagaId}]: Executing step [{$step->getName()}]");

                $state = $step->execute($state);
                $state->markStepCompleted($step->getName());
                $executedSteps[] = $step;

                $this->persistSagaState($state, SagaState::STATUS_RUNNING);
            }

            $state->markCompleted();
            $this->persistSagaState($state, SagaState::STATUS_COMPLETED);

            Log::info("Saga [{$sagaId}]: Completed successfully");
        } catch (\Throwable $e) {
            Log::error("Saga [{$sagaId}]: Failed — beginning compensation", [
                'error'      => $e->getMessage(),
                'step_count' => count($executedSteps),
            ]);

            $state->markFailed($e->getMessage());
            $state->markCompensating();
            $this->persistSagaState($state, SagaState::STATUS_COMPENSATING);

            $this->compensate(array_reverse($executedSteps), $state);
        }

        return $state;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function compensate(array $steps, SagaState $state): void
    {
        foreach ($steps as $step) {
            if (!$step->canCompensate()) {
                Log::info("Saga [{$state->getSagaId()}]: Skipping compensation for [{$step->getName()}] (not compensatable)");
                continue;
            }

            try {
                Log::info("Saga [{$state->getSagaId()}]: Compensating step [{$step->getName()}]");
                $step->compensate($state);
                $state->markStepCompensated($step->getName());
            } catch (\Throwable $e) {
                // Log but continue compensating remaining steps
                Log::error("Saga [{$state->getSagaId()}]: Compensation failed for [{$step->getName()}]", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $state->markCompensated();
        $this->persistSagaState($state, SagaState::STATUS_COMPENSATED);

        Log::info("Saga [{$state->getSagaId()}]: Compensation complete");
    }

    private function persistSagaState(SagaState $state, string $status): void
    {
        try {
            SagaStateRecord::updateOrCreate(
                ['saga_id' => $state->getSagaId()],
                [
                    'status'            => $status,
                    'payload'           => $state->getPayload(),
                    'context'           => $state->getContext(),
                    'completed_steps'   => $state->getCompletedSteps(),
                    'compensated_steps' => $state->getCompensatedSteps(),
                    'events'            => $state->getEvents(),
                    'failure_reason'    => $state->getFailureReason(),
                ]
            );
        } catch (\Throwable $e) {
            // Persistence failure must never abort the saga itself
            Log::error('Failed to persist saga state', [
                'saga_id' => $state->getSagaId(),
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
