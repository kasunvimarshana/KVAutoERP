<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

interface GenerateFinancialReportServiceInterface
{
    /**
     * @return array{assets: array, liabilities: array, equity: array, net_assets: float}
     */
    public function generateBalanceSheet(int $tenantId, \DateTimeInterface $asOfDate): array;

    /**
     * @return array{income: array, expenses: array, net_profit: float}
     */
    public function generateProfitAndLoss(int $tenantId, \DateTimeInterface $fromDate, \DateTimeInterface $toDate): array;
}
