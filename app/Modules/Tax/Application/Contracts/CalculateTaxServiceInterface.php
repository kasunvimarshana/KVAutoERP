<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Contracts;

interface CalculateTaxServiceInterface
{
    /**
     * @return array{tax_amount: float, breakdown: array<int, array{name: string, rate: float, amount: float}>}
     */
    public function calculate(int $taxGroupId, float $subtotal): array;
}
