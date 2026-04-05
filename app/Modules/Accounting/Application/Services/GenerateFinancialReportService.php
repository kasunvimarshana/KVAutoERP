<?php declare(strict_types=1);
namespace Modules\Accounting\Application\Services;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
class GenerateFinancialReportService {
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepo,
        private readonly JournalEntryRepositoryInterface $journalRepo,
    ) {}
    public function generateBalanceSheet(int $tenantId, string $asOf): array {
        $accounts = $this->accountRepo->findByTenant($tenantId);
        $assets = array_filter($accounts, fn($a) => $a->getType() === 'asset');
        $liabilities = array_filter($accounts, fn($a) => $a->getType() === 'liability');
        $equity = array_filter($accounts, fn($a) => $a->getType() === 'equity');
        return [
            'type' => 'balance_sheet',
            'as_of' => $asOf,
            'assets' => array_values(array_map(fn($a) => ['id'=>$a->getId(),'name'=>$a->getName(),'code'=>$a->getCode()], $assets)),
            'liabilities' => array_values(array_map(fn($a) => ['id'=>$a->getId(),'name'=>$a->getName(),'code'=>$a->getCode()], $liabilities)),
            'equity' => array_values(array_map(fn($a) => ['id'=>$a->getId(),'name'=>$a->getName(),'code'=>$a->getCode()], $equity)),
        ];
    }
    public function generateProfitAndLoss(int $tenantId, string $from, string $to): array {
        $accounts = $this->accountRepo->findByTenant($tenantId);
        $income = array_filter($accounts, fn($a) => $a->getType() === 'income');
        $expenses = array_filter($accounts, fn($a) => $a->getType() === 'expense');
        return [
            'type' => 'profit_and_loss',
            'from' => $from,
            'to' => $to,
            'income' => array_values(array_map(fn($a) => ['id'=>$a->getId(),'name'=>$a->getName(),'code'=>$a->getCode()], $income)),
            'expenses' => array_values(array_map(fn($a) => ['id'=>$a->getId(),'name'=>$a->getName(),'code'=>$a->getCode()], $expenses)),
        ];
    }
}
