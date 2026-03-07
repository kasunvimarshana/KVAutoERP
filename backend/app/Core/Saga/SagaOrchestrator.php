<?php

namespace App\Core\Saga;

use Illuminate\Support\Facades\Log;

/**
 * Orchestrates a sequence of SagaStep instances.
 *
 * On any step failure the orchestrator runs compensating transactions in
 * reverse order for all previously completed steps before re-throwing.
 */
class SagaOrchestrator
{
    /** @var SagaStep[] */
    protected array $steps = [];

    /** @var SagaStep[] */
    protected array $executedSteps = [];

    /** @var array<string,mixed> */
    protected array $context = [];

    public function addStep(SagaStep $step): static
    {
        $this->steps[] = $step;

        return $this;
    }

    /**
     * @param  array<string,mixed>  $initialContext
     * @return array<string,mixed>  Final merged context after all steps succeed.
     *
     * @throws SagaException
     */
    public function execute(array $initialContext = []): array
    {
        $this->context       = $initialContext;
        $this->executedSteps = [];

        foreach ($this->steps as $step) {
            try {
                Log::info("Saga: executing step '{$step->getName()}'");
                $result        = $step->execute($this->context);
                $this->context = array_merge($this->context, $result);
                $this->executedSteps[] = $step;
                Log::info("Saga: step '{$step->getName()}' succeeded");
            } catch (\Throwable $e) {
                Log::error("Saga: step '{$step->getName()}' failed – {$e->getMessage()}");
                $this->rollback();
                throw new SagaException(
                    "Saga failed at step '{$step->getName()}': {$e->getMessage()}",
                    $e->getCode(),
                    $e
                );
            }
        }

        return $this->context;
    }

    protected function rollback(): void
    {
        foreach (array_reverse($this->executedSteps) as $step) {
            try {
                Log::info("Saga: compensating step '{$step->getName()}'");
                $step->compensate($this->context);
                Log::info("Saga: compensation for '{$step->getName()}' done");
            } catch (\Throwable $e) {
                Log::error("Saga: compensation for '{$step->getName()}' failed – {$e->getMessage()}");
            }
        }
    }

    /** @return array<string,mixed> */
    public function getContext(): array
    {
        return $this->context;
    }
}
