<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Domain\Entities\TaxGroupRate;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;

class CalculateTaxService implements CalculateTaxServiceInterface
{
    public function __construct(
        private readonly TaxGroupRepositoryInterface $taxGroupRepository,
        private readonly TaxGroupRateRepositoryInterface $taxGroupRateRepository,
    ) {}

    public function calculate(int $taxGroupId, float $subtotal): array
    {
        $group = $this->taxGroupRepository->findById($taxGroupId);

        if ($group === null) {
            throw new NotFoundException('TaxGroup', $taxGroupId);
        }

        $rates = $this->taxGroupRateRepository->findByTaxGroup($taxGroupId);

        // Filter active rates and sort by priority ascending
        $activeRates = array_filter($rates, fn (TaxGroupRate $r) => $r->isActive());
        usort($activeRates, fn (TaxGroupRate $a, TaxGroupRate $b) => $a->getPriority() <=> $b->getPriority());

        $totalTax = 0.0;
        $breakdown = [];
        $runningBase = $subtotal;

        foreach ($activeRates as $rate) {
            // If compound, each rate applies on (subtotal + previously accumulated taxes)
            $base = $group->isCompound() ? $runningBase : $subtotal;
            $taxAmount = $rate->calculateTax($base);

            $breakdown[] = [
                'name'   => $rate->getName(),
                'rate'   => $rate->getRate(),
                'amount' => $taxAmount,
            ];

            $totalTax += $taxAmount;
            $runningBase += $taxAmount;
        }

        return [
            'tax_amount' => $totalTax,
            'breakdown'  => $breakdown,
        ];
    }
}
