<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Contracts;

interface CalculateTaxServiceInterface
{
    /**
     * @return array{base: float, tax: float, total: float, breakdown: array<int, array{name: string, rate: float, tax: float}>}
     */
    public function calculate(float $amount, int $taxGroupId, int $tenantId): array;
}
