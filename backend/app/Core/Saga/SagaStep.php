<?php

namespace App\Core\Saga;

abstract class SagaStep
{
    /**
     * Execute this step and return additional context to be merged.
     *
     * @param  array<string,mixed>  $context
     * @return array<string,mixed>
     */
    abstract public function execute(array $context): array;

    /**
     * Compensate (roll back) this step.
     *
     * @param  array<string,mixed>  $context
     */
    abstract public function compensate(array $context): void;

    abstract public function getName(): string;
}
