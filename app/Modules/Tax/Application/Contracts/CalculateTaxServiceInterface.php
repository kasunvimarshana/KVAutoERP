<?php declare(strict_types=1);
namespace Modules\Tax\Application\Contracts;
interface CalculateTaxServiceInterface {
    /** Returns ['tax_amount' => float, 'breakdown' => array] */
    public function calculate(float $amount, array $rates, bool $isCompound = false): array;
}
