<?php

namespace Shared\Core\Saga;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Shared\Core\Contracts\SagaStepInterface;

class SagaOrchestrator
{
    /**
     * @var Collection
     */
    protected $steps;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var array
     */
    protected $compensations = [];

    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->steps = collect();
    }

    /**
     * Add a step to the Saga
     *
     * @param SagaStepInterface $step
     * @return self
     */
    public function addStep(SagaStepInterface $step): self
    {
        $this->steps->push($step);
        return $this;
    }

    /**
     * Execute the Saga
     *
     * @param array $initialData
     * @return bool
     */
    public function execute(array $initialData): bool
    {
        Log::info("Starting Saga [{$this->id}]");

        $currentData = $initialData;

        foreach ($this->steps as $index => $step) {
            try {
                Log::info("Executing Saga Step: " . get_class($step));
                $result = $step->handle($currentData);

                if ($result === false) {
                    throw new \Exception("Step failed: " . get_class($step));
                }

                $this->results[$index] = $result;
                $currentData = array_merge($currentData, is_array($result) ? $result : []);

                // Store compensation logic
                $this->compensations[$index] = function() use ($step, $currentData) {
                    Log::info("Compensating Saga Step: " . get_class($step));
                    return $step->rollback($currentData);
                };

            } catch (\Exception $e) {
                Log::error("Saga failed at step " . get_class($step) . ": " . $e->getMessage());
                $this->rollback($index);
                return false;
            }
        }

        Log::info("Saga [{$this->id}] completed successfully");
        return true;
    }

    /**
     * Rollback the Saga steps in reverse order
     *
     * @param int $failedStepIndex
     * @return void
     */
    protected function rollback(int $failedStepIndex): void
    {
        Log::warning("Starting Rollback for Saga [{$this->id}]");

        for ($i = $failedStepIndex; $i >= 0; $i--) {
            if (isset($this->compensations[$i])) {
                try {
                    $this->compensations[$i]();
                } catch (\Exception $e) {
                    Log::error("Compensation failed for step {$i}: " . $e->getMessage());
                    // In a production-ready system, failed compensations should be retried or flagged for manual intervention
                }
            }
        }

        Log::warning("Rollback for Saga [{$this->id}] finished");
    }

    /**
     * Get the results of each step
     *
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
