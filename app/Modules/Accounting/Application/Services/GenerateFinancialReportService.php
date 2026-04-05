<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\GenerateFinancialReportServiceInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;

class GenerateFinancialReportService implements GenerateFinancialReportServiceInterface
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepository,
        private readonly JournalEntryRepositoryInterface $journalRepository,
    ) {}

    public function generateBalanceSheet(string $tenantId, string $asOf): array
    {
        $accounts = $this->accountRepository->allByTenant($tenantId);

        $assets      = [];
        $liabilities = [];
        $equity      = [];

        foreach ($accounts as $account) {
            $item = [
                'id'      => $account->getId(),
                'code'    => $account->getCode(),
                'name'    => $account->getName(),
                'balance' => $account->getCurrentBalance(),
            ];

            if (in_array($account->getType(), ['asset', 'bank', 'credit_card', 'accounts_receivable'], true)) {
                $assets[] = $item;
            } elseif (in_array($account->getType(), ['liability', 'accounts_payable'], true)) {
                $liabilities[] = $item;
            } elseif ($account->getType() === 'equity') {
                $equity[] = $item;
            }
        }

        $totalAssets      = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity      = array_sum(array_column($equity, 'balance'));

        return [
            'as_of'       => $asOf,
            'tenant_id'   => $tenantId,
            'assets'      => ['items' => $assets, 'total' => $totalAssets],
            'liabilities' => ['items' => $liabilities, 'total' => $totalLiabilities],
            'equity'      => ['items' => $equity, 'total' => $totalEquity],
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'balanced'    => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    public function generateProfitLoss(string $tenantId, string $from, string $to): array
    {
        $accounts = $this->accountRepository->allByTenant($tenantId);

        $income   = [];
        $expenses = [];

        foreach ($accounts as $account) {
            $item = [
                'id'      => $account->getId(),
                'code'    => $account->getCode(),
                'name'    => $account->getName(),
                'balance' => $account->getCurrentBalance(),
            ];

            if ($account->getType() === 'income') {
                $income[] = $item;
            } elseif ($account->getType() === 'expense') {
                $expenses[] = $item;
            }
        }

        $totalIncome   = array_sum(array_column($income, 'balance'));
        $totalExpenses = array_sum(array_column($expenses, 'balance'));
        $netProfit     = $totalIncome - $totalExpenses;

        return [
            'from'           => $from,
            'to'             => $to,
            'tenant_id'      => $tenantId,
            'income'         => ['items' => $income, 'total' => $totalIncome],
            'expenses'       => ['items' => $expenses, 'total' => $totalExpenses],
            'net_profit'     => $netProfit,
            'is_profitable'  => $netProfit >= 0,
        ];
    }

    public function generateCashFlow(string $tenantId, string $from, string $to): array
    {
        $bankAccounts = $this->accountRepository->getByType('bank', $tenantId);
        $creditCards  = $this->accountRepository->getByType('credit_card', $tenantId);

        $inflow  = 0.0;
        $outflow = 0.0;

        foreach ($bankAccounts->merge($creditCards) as $account) {
            $balance = $account->getCurrentBalance();
            if ($balance > 0) {
                $inflow += $balance;
            } else {
                $outflow += abs($balance);
            }
        }

        return [
            'from'             => $from,
            'to'               => $to,
            'tenant_id'        => $tenantId,
            'operating_inflow' => $inflow,
            'operating_outflow'=> $outflow,
            'net_cash_flow'    => $inflow - $outflow,
        ];
    }
}
