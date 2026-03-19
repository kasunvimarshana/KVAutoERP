<?php

namespace Shared\Core\Contracts;

interface SagaStepInterface
{
    /**
     * Handle the Saga step
     *
     * @param array $data
     * @return array|bool
     */
    public function handle(array $data): bool|array;

    /**
     * Rollback the Saga step
     *
     * @param array $data
     * @return bool
     */
    public function rollback(array $data): bool;
}
