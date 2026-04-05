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
        private readonly JournalEntryRepositoryInterface $journalEntryRepository,
    ) {}

    public function generateBalanceSheet(int $tenantId, \DateTimeInterface $asOfDate): array
    {
        $accounts = $this->accountRepository->all($tenantId);
        $entries  = $this->journalEntryRepository->findByDateRange(
            $tenantId,
            new \DateTimeImmutable('1970-01-01'),
            $asOfDate,
        );

        $balances = $this->computeAccountBalances($entries);

        $assets      = [];
        $liabilities = [];
        $equity      = [];

        foreach ($accounts as $account) {
            $accountId = $account->getId();
            $balance   = $balances[$accountId] ?? 0.0;

            $row = [
                'id'      => $accountId,
                'code'    => $account->getCode(),
                'name'    => $account->getName(),
                'balance' => $balance,
            ];

            match ($account->getType()) {
                'asset', 'bank', 'credit_card', 'accounts_receivable' => $assets[] = $row,
                'liability', 'accounts_payable'                        => $liabilities[] = $row,
                'equity'                                               => $equity[] = $row,
                default                                                => null,
            };
        }

        $totalAssets      = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity      = array_sum(array_column($equity, 'balance'));

        return [
            'assets'      => $assets,
            'liabilities' => $liabilities,
            'equity'      => $equity,
            'net_assets'  => $totalAssets - $totalLiabilities - $totalEquity,
        ];
    }

    public function generateProfitAndLoss(int $tenantId, \DateTimeInterface $fromDate, \DateTimeInterface $toDate): array
    {
        $accounts = $this->accountRepository->all($tenantId);
        $entries  = $this->journalEntryRepository->findByDateRange($tenantId, $fromDate, $toDate);

        $balances = $this->computeAccountBalances($entries);

        $income   = [];
        $expenses = [];

        foreach ($accounts as $account) {
            $accountId = $account->getId();
            $balance   = $balances[$accountId] ?? 0.0;

            $row = [
                'id'      => $accountId,
                'code'    => $account->getCode(),
                'name'    => $account->getName(),
                'balance' => $balance,
            ];

            match ($account->getType()) {
                'income'  => $income[] = $row,
                'expense' => $expenses[] = $row,
                default   => null,
            };
        }

        $totalIncome   = array_sum(array_column($income, 'balance'));
        $totalExpenses = array_sum(array_column($expenses, 'balance'));

        return [
            'income'     => $income,
            'expenses'   => $expenses,
            'net_profit' => $totalIncome - $totalExpenses,
        ];
    }

    /**
     * Computes net balance per account_id from posted journal entries.
     * Credit-normal accounts: balance = credits - debits.
     * Debit-normal accounts:  balance = debits - credits.
     *
     * @return array<int, float>
     */
    private function computeAccountBalances(array $entries): array
    {
        $balances = [];

        foreach ($entries as $entry) {
            if ($entry->getStatus() !== 'posted') {
                continue;
            }

            foreach ($entry->getLines() as $line) {
                $accountId = $line->getAccountId();

                if (! isset($balances[$accountId])) {
                    $balances[$accountId] = 0.0;
                }

                $balances[$accountId] += $line->getDebit() - $line->getCredit();
            }
        }

        return $balances;
    }
}
