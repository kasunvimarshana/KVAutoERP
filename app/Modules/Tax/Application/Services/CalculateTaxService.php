<?php declare(strict_types=1);
namespace Modules\Tax\Application\Services;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
class CalculateTaxService implements CalculateTaxServiceInterface {
    public function calculate(float $amount, array $rates, bool $isCompound = false): array {
        $breakdown = [];
        $totalTax = 0.0;
        $base = $amount;
        foreach ($rates as $rate) {
            $taxAmount = round($base * ($rate['rate'] / 100.0), 2);
            $breakdown[] = ['name' => $rate['name'], 'rate' => $rate['rate'], 'tax_amount' => $taxAmount];
            $totalTax += $taxAmount;
            if ($isCompound) $base += $taxAmount;
        }
        return ['tax_amount' => round($totalTax, 2), 'breakdown' => $breakdown];
    }
}
