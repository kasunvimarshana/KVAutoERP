<?php

namespace App\Saga;

use Illuminate\Support\Facades\Log;
use Throwable;

class SagaOrchestrator
{
    public function execute(array $steps, callable $finalResult): mixed
    {
        $completed = [];

        try {
            foreach ($steps as $index => $step) {
                ($step['action'])();
                $completed[] = $step['compensate'];
                Log::debug('Saga step completed', ['step' => $index]);
            }

            return $finalResult();
        } catch (Throwable $e) {
            Log::error('Saga step failed — running compensations', [
                'error'             => $e->getMessage(),
                'completed_steps'   => count($completed),
            ]);

            foreach (array_reverse($completed) as $compensate) {
                try {
                    $compensate();
                } catch (Throwable $ce) {
                    Log::critical('Compensation action failed', ['error' => $ce->getMessage()]);
                }
            }

            throw $e;
        }
    }
}
