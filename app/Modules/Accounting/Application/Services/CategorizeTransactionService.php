<?php declare(strict_types=1);
namespace Modules\Accounting\Application\Services;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
class CategorizeTransactionService {
    public function __construct(
        private readonly BankTransactionRepositoryInterface $txnRepo,
        private readonly TransactionRuleRepositoryInterface $ruleRepo,
    ) {}
    public function categorize(int $tenantId): int {
        $transactions = $this->txnRepo->findUncategorized($tenantId);
        $rules = $this->ruleRepo->findByTenant($tenantId);
        usort($rules, fn($a,$b) => $a->getPriority() <=> $b->getPriority());
        $count = 0;
        foreach ($transactions as $txn) {
            foreach ($rules as $rule) {
                if ($rule->matches($txn)) {
                    $categorized = new BankTransaction($txn->getId(),$txn->getBankAccountId(),$txn->getTenantId(),$txn->getType(),$txn->getAmount(),$txn->getTransactionDate(),$txn->getDescription(),'categorized',$txn->getSource(),$rule->getCategoryAccountId(),$txn->getReference());
                    $this->txnRepo->save($categorized);
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }
}
