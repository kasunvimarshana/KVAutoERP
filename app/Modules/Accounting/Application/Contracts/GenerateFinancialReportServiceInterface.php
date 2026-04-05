<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

interface GenerateFinancialReportServiceInterface
{
    /**
     * Returns a Balance Sheet snapshot as of the given date.
     *
     * @return array{
     *   assets: array<int, array{account_id: int, name: string, balance: float}>,
     *   liabilities: array<int, array{account_id: int, name: string, balance: float}>,
     *   equity: array<int, array{account_id: int, name: string, balance: float}>,
     *   total_assets: float,
     *   total_liabilities: float,
     *   total_equity: float,
     * }
     */
    public function generateBalanceSheet(int $tenantId, string $asOfDate): array;

    /**
     * Returns a Profit & Loss statement for the given date range.
     *
     * @return array{
     *   income: array<int, array{account_id: int, name: string, balance: float}>,
     *   expenses: array<int, array{account_id: int, name: string, balance: float}>,
     *   total_income: float,
     *   total_expenses: float,
     *   net_profit: float,
     * }
     */
    public function generateProfitAndLoss(int $tenantId, string $startDate, string $endDate): array;
}
