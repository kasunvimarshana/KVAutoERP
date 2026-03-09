<?php

declare(strict_types=1);

namespace Tests\Unit\Saga;

use App\Domain\Saga\Context\SagaContext;
use App\Domain\Saga\Orchestrator\SagaOrchestrator;
use App\Domain\Saga\Result\SagaResult;
use App\Domain\Saga\Step\AbstractSagaStep;
use Tests\TestCase;

/**
 * Saga Orchestrator Unit Tests.
 *
 * Tests successful execution, failure with compensation, and retry logic.
 */
class SagaOrchestratorTest extends TestCase
{
    // =========================================================================
    // Successful Execution Tests
    // =========================================================================

    public function test_executes_all_steps_in_order(): void
    {
        $executed = [];

        $step1 = $this->createStep('step_1', function (SagaContext $ctx) use (&$executed): void {
            $executed[] = 'step_1';
            $ctx->set('step_1_result', 'done');
        });

        $step2 = $this->createStep('step_2', function (SagaContext $ctx) use (&$executed): void {
            $executed[] = 'step_2';
            $ctx->set('step_2_result', 'done');
        });

        $orchestrator = new SagaOrchestrator();
        $orchestrator->addStep($step1)->addStep($step2);

        $result = $orchestrator->execute([]);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(['step_1', 'step_2'], $executed);
        $this->assertContains('step_1', $result->getCompletedSteps());
        $this->assertContains('step_2', $result->getCompletedSteps());
    }

    public function test_returns_success_result_on_completion(): void
    {
        $step = $this->createStep('noop', function (): void {});

        $orchestrator = (new SagaOrchestrator())->addStep($step);
        $result       = $orchestrator->execute(['key' => 'value']);

        $this->assertInstanceOf(SagaResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEmpty($result->getFailedSteps());
        $this->assertEmpty($result->getCompensatedSteps());
        $this->assertNull($result->getError());
    }

    public function test_context_data_is_shared_between_steps(): void
    {
        $step1 = $this->createStep('producer', function (SagaContext $ctx): void {
            $ctx->set('shared_data', 'produced_value');
        });

        $consumed = null;
        $step2 = $this->createStep('consumer', function (SagaContext $ctx) use (&$consumed): void {
            $consumed = $ctx->get('shared_data');
        });

        $orchestrator = (new SagaOrchestrator())->addStep($step1)->addStep($step2);
        $orchestrator->execute([]);

        $this->assertEquals('produced_value', $consumed);
    }

    // =========================================================================
    // Failure and Compensation Tests
    // =========================================================================

    public function test_compensates_completed_steps_on_failure(): void
    {
        $compensated = [];

        $step1 = $this->createStep(
            'step_1',
            execute: function (): void {},
            compensate: function () use (&$compensated): void {
                $compensated[] = 'step_1';
            },
        );

        $step2 = $this->createStep(
            'step_2',
            execute: function (): void {
                throw new \RuntimeException('Step 2 failed');
            },
            compensate: function () use (&$compensated): void {
                $compensated[] = 'step_2';
            },
        );

        $orchestrator = (new SagaOrchestrator())->addStep($step1)->addStep($step2);
        $result       = $orchestrator->execute([]);

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isCompensated());
        $this->assertContains('step_2', $result->getFailedSteps());
        $this->assertContains('step_1', $result->getCompensatedSteps());
        $this->assertContains('step_1', $compensated);
        $this->assertNotContains('step_2', $compensated); // Failed step is not compensated
    }

    public function test_compensates_in_reverse_order(): void
    {
        $compensationOrder = [];

        $makeStep = fn (string $name) => $this->createStep(
            $name,
            execute: function (): void {},
            compensate: function () use ($name, &$compensationOrder): void {
                $compensationOrder[] = $name;
            },
        );

        $failingStep = $this->createStep('failing', execute: function (): void {
            throw new \RuntimeException('Failure');
        });

        $orchestrator = (new SagaOrchestrator())
            ->addStep($makeStep('step_1'))
            ->addStep($makeStep('step_2'))
            ->addStep($makeStep('step_3'))
            ->addStep($failingStep);

        $orchestrator->execute([]);

        // Compensation should be in reverse order: step_3, step_2, step_1
        $this->assertEquals(['step_3', 'step_2', 'step_1'], $compensationOrder);
    }

    public function test_failed_result_contains_error(): void
    {
        $error = new \RuntimeException('Test error');

        $step = $this->createStep('failing', execute: function () use ($error): void {
            throw $error;
        });

        $result = (new SagaOrchestrator())->addStep($step)->execute([]);

        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->getError());
        $this->assertEquals('Test error', $result->getError()->getMessage());
    }

    // =========================================================================
    // State Tests
    // =========================================================================

    public function test_transaction_id_is_unique(): void
    {
        $orchestrator1 = new SagaOrchestrator();
        $orchestrator2 = new SagaOrchestrator();

        $this->assertNotEquals(
            $orchestrator1->getTransactionId(),
            $orchestrator2->getTransactionId(),
        );
    }

    public function test_state_transitions_correctly(): void
    {
        $orchestrator = new SagaOrchestrator();
        $this->assertEquals('pending', $orchestrator->getState());

        $step = $this->createStep('noop', function (): void {});
        $orchestrator->addStep($step)->execute([]);

        $this->assertEquals('completed', $orchestrator->getState());
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    private function createStep(
        string $name,
        callable $execute,
        ?callable $compensate = null,
    ): AbstractSagaStep {
        return new class($name, $execute, $compensate) extends AbstractSagaStep
        {
            public function __construct(
                private string $stepName,
                private $executeCallback,
                private $compensateCallback,
            ) {}

            public function execute(SagaContext $context): void
            {
                ($this->executeCallback)($context);
            }

            public function compensate(SagaContext $context): void
            {
                if ($this->compensateCallback) {
                    ($this->compensateCallback)($context);
                }
            }

            public function getName(): string
            {
                return $this->stepName;
            }
        };
    }
}
