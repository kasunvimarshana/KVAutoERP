<?php

declare(strict_types=1);

namespace App\Contracts\Health;

interface HealthCheckInterface
{
    /**
     * Run the health check and return result.
     */
    public function check(): HealthCheckResult;

    /**
     * Return the name of this health check.
     */
    public function name(): string;
}
