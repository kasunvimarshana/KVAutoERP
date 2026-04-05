<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Contracts;

interface CalculateTaxServiceInterface
{
    /**
     * Calculate tax for a given tax group and amount.
     *
     * @return array{breakdown: array<int, array{name: string, rate: float, amount: float}>, total: float}
     */
    public function calculate(int $taxGroupId, float $amount, int $tenantId): array;
}
