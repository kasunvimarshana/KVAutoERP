<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

interface GenerateFinancialReportServiceInterface
{
    public function generateBalanceSheet(string $tenantId, string $asOf): array;
    public function generateProfitLoss(string $tenantId, string $from, string $to): array;
    public function generateCashFlow(string $tenantId, string $from, string $to): array;
}
