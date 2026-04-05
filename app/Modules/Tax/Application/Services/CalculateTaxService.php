<?php

declare(strict_types=1);

namespace Modules\Tax\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;

class CalculateTaxService implements CalculateTaxServiceInterface
{
    public function __construct(
        private readonly TaxGroupRepositoryInterface $groupRepository,
        private readonly TaxGroupRateRepositoryInterface $rateRepository,
    ) {}

    public function calculate(float $amount, int $taxGroupId, int $tenantId): array
    {
        $group = $this->groupRepository->findById($taxGroupId, $tenantId);

        if ($group === null) {
            throw new NotFoundException("TaxGroup #{$taxGroupId} not found.");
        }

        $rates = $this->rateRepository->listForGroup($taxGroupId);

        usort($rates, fn ($a, $b) => $a->getOrder() <=> $b->getOrder());

        $base = $amount;
        $totalTax = 0.0;
        $runningBase = $base;
        $breakdown = [];

        foreach ($rates as $rateEntity) {
            $taxableBase = $rateEntity->isCompound() ? $runningBase : $base;
            $tax = round($taxableBase * ($rateEntity->getRate() / 100), 10);
            $totalTax += $tax;
            $runningBase += $tax;

            $breakdown[] = [
                'name' => $rateEntity->getName(),
                'rate' => $rateEntity->getRate(),
                'tax'  => $tax,
            ];
        }

        return [
            'base'      => $base,
            'tax'       => $totalTax,
            'total'     => $base + $totalTax,
            'breakdown' => $breakdown,
        ];
    }
}
