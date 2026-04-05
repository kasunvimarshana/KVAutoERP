<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\GenerateFinancialReportServiceInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalLineModel;

final class GenerateFinancialReportService implements GenerateFinancialReportServiceInterface
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepository,
        private readonly JournalLineModel $journalLineModel,
    ) {}

    public function generateBalanceSheet(int $tenantId, string $asOfDate): array
    {
        $types = [
            Account::TYPE_ASSET,
            Account::TYPE_LIABILITY,
            Account::TYPE_EQUITY,
            Account::TYPE_BANK,
            Account::TYPE_CREDIT_CARD,
            Account::TYPE_AP,
            Account::TYPE_AR,
        ];

        $rows = $this->aggregateByAccountType($tenantId, $types, null, $asOfDate);

        $assetTypes     = [Account::TYPE_ASSET, Account::TYPE_BANK, Account::TYPE_AR];
        $liabilityTypes = [Account::TYPE_LIABILITY, Account::TYPE_CREDIT_CARD, Account::TYPE_AP];
        $equityTypes    = [Account::TYPE_EQUITY];

        $assets      = $this->filterAndSum($rows, $assetTypes);
        $liabilities = $this->filterAndSum($rows, $liabilityTypes);
        $equity      = $this->filterAndSum($rows, $equityTypes);

        return [
            'assets'            => $assets['rows'],
            'liabilities'       => $liabilities['rows'],
            'equity'            => $equity['rows'],
            'total_assets'      => $assets['total'],
            'total_liabilities' => $liabilities['total'],
            'total_equity'      => $equity['total'],
        ];
    }

    public function generateProfitAndLoss(int $tenantId, string $startDate, string $endDate): array
    {
        $types = [Account::TYPE_INCOME, Account::TYPE_EXPENSE];

        $rows = $this->aggregateByAccountType($tenantId, $types, $startDate, $endDate);

        $income   = $this->filterAndSum($rows, [Account::TYPE_INCOME]);
        $expenses = $this->filterAndSum($rows, [Account::TYPE_EXPENSE]);

        return [
            'income'          => $income['rows'],
            'expenses'        => $expenses['rows'],
            'total_income'    => $income['total'],
            'total_expenses'  => $expenses['total'],
            'net_profit'      => $income['total'] - $expenses['total'],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Aggregates journal line totals joined to accounts, filtered by account types
     * and an optional date range on the parent journal entry.
     *
     * @param array<string> $types
     *
     * @return array<int, array{account_id: int, name: string, type: string, balance: float}>
     */
    private function aggregateByAccountType(
        int $tenantId,
        array $types,
        ?string $startDate,
        string $endDate,
    ): array {
        $query = $this->journalLineModel
            ->newQueryWithoutScopes()
            ->select([
                'accounting_accounts.id as account_id',
                'accounting_accounts.name',
                'accounting_accounts.type',
                'accounting_accounts.normal_balance',
                \Illuminate\Support\Facades\DB::raw('SUM(accounting_journal_lines.debit) as total_debit'),
                \Illuminate\Support\Facades\DB::raw('SUM(accounting_journal_lines.credit) as total_credit'),
            ])
            ->join(
                'accounting_accounts',
                'accounting_journal_lines.account_id',
                '=',
                'accounting_accounts.id'
            )
            ->join(
                'accounting_journal_entries',
                'accounting_journal_lines.journal_entry_id',
                '=',
                'accounting_journal_entries.id'
            )
            ->where('accounting_journal_entries.tenant_id', $tenantId)
            ->where('accounting_journal_entries.status', 'posted')
            ->whereIn('accounting_accounts.type', $types)
            ->where('accounting_journal_entries.date', '<=', $endDate)
            ->groupBy(
                'accounting_accounts.id',
                'accounting_accounts.name',
                'accounting_accounts.type',
                'accounting_accounts.normal_balance',
            );

        if ($startDate !== null) {
            $query->where('accounting_journal_entries.date', '>=', $startDate);
        }

        $results = $query->get();

        return $results->map(function ($row): array {
            $debit  = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            // Net balance from the account's perspective
            $balance = $row->normal_balance === Account::NORMAL_DEBIT
                ? $debit - $credit
                : $credit - $debit;

            return [
                'account_id' => (int) $row->account_id,
                'name'       => $row->name,
                'type'       => $row->type,
                'balance'    => round($balance, 6),
            ];
        })->all();
    }

    /**
     * @param array<int, array{account_id: int, name: string, type: string, balance: float}> $rows
     * @param array<string> $types
     *
     * @return array{rows: array<int, array{account_id: int, name: string, balance: float}>, total: float}
     */
    private function filterAndSum(array $rows, array $types): array
    {
        $filtered = array_filter($rows, fn (array $r) => in_array($r['type'], $types, true));
        $filtered = array_values($filtered);

        $total = (float) array_sum(array_column($filtered, 'balance'));

        // Strip 'type' from the output rows
        $out = array_map(fn (array $r) => [
            'account_id' => $r['account_id'],
            'name'       => $r['name'],
            'balance'    => $r['balance'],
        ], $filtered);

        return ['rows' => $out, 'total' => round($total, 6)];
    }
}
