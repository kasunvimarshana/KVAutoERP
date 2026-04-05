<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxRateRepositoryInterface;

class CalculateTaxService implements CalculateTaxServiceInterface
{
    public function __construct(
        private readonly TaxGroupRateRepositoryInterface $groupRateRepo,
        private readonly TaxRateRepositoryInterface $taxRateRepo,
    ) {}

    public function calculate(int $taxGroupId, float $amount, int $tenantId): array
    {
        $groupRates = $this->groupRateRepo->findByGroup($taxGroupId, $tenantId);

        if (empty($groupRates)) {
            return ['breakdown' => [], 'total' => 0.0];
        }

        // Sort by sortOrder ascending
        usort($groupRates, fn ($a, $b) => $a->sortOrder <=> $b->sortOrder);

        $breakdown        = [];
        $previousTaxTotal = 0.0;

        foreach ($groupRates as $groupRate) {
            $taxRate = $this->taxRateRepo->findById($groupRate->taxRateId, $tenantId);

            if ($taxRate === null || !$taxRate->isActive) {
                continue;
            }

            if ($taxRate->type === 'fixed') {
                $taxAmount = $taxRate->rate;
            } elseif ($taxRate->isCompound) {
                $taxAmount = ($amount + $previousTaxTotal) * ($taxRate->rate / 100);
            } else {
                $taxAmount = $amount * ($taxRate->rate / 100);
            }

            $taxAmount = round($taxAmount, 4);

            $breakdown[] = [
                'name'   => $taxRate->name,
                'code'   => $taxRate->code,
                'rate'   => $taxRate->rate,
                'type'   => $taxRate->type,
                'amount' => $taxAmount,
            ];

            $previousTaxTotal += $taxAmount;
        }

        $total = round(array_sum(array_column($breakdown, 'amount')), 4);

        return [
            'breakdown' => $breakdown,
            'total'     => $total,
        ];
    }
}
