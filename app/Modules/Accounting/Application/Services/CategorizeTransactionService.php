<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\CategorizeTransactionServiceInterface;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class CategorizeTransactionService implements CategorizeTransactionServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $transactionRepository,
        private readonly TransactionRuleRepositoryInterface $ruleRepository,
    ) {}

    public function categorize(int $transactionId, ?int $categoryId, ?int $accountId): BankTransaction
    {
        $transaction = $this->transactionRepository->findById($transactionId);

        if ($transaction === null) {
            throw new NotFoundException('BankTransaction', $transactionId);
        }

        $updated = $this->transactionRepository->update($transactionId, [
            'category'   => $categoryId !== null ? (string) $categoryId : $transaction->getCategory(),
            'account_id' => $accountId ?? $transaction->getAccountId(),
            'status'     => 'categorized',
        ]);

        return $updated ?? $transaction;
    }

    public function autoCategorize(int $tenantId): int
    {
        $rules = $this->ruleRepository->findActive($tenantId);
        $pending = $this->transactionRepository->findByStatus($tenantId, 'pending');
        $categorized = 0;

        foreach ($pending as $transaction) {
            foreach ($rules as $rule) {
                if ($this->ruleApplies($rule, $transaction) && $this->conditionsMatch($rule, $transaction)) {
                    $this->transactionRepository->update($transaction->getId(), [
                        'category'   => $rule->getCategoryId() !== null ? (string) $rule->getCategoryId() : $transaction->getCategory(),
                        'account_id' => $rule->getAccountId() ?? $transaction->getAccountId(),
                        'status'     => 'categorized',
                    ]);
                    ++$categorized;
                    break; // First matching rule wins
                }
            }
        }

        return $categorized;
    }

    private function ruleApplies(TransactionRule $rule, BankTransaction $transaction): bool
    {
        return match ($rule->getApplyTo()) {
            'debit'  => $transaction->getType() === 'debit',
            'credit' => $transaction->getType() === 'credit',
            default  => true,
        };
    }

    private function conditionsMatch(TransactionRule $rule, BankTransaction $transaction): bool
    {
        foreach ($rule->getConditions() as $condition) {
            $field    = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value    = $condition['value'] ?? '';

            $txValue = match ($field) {
                'description' => $transaction->getDescription(),
                'amount'      => (string) $transaction->getAmount(),
                'type'        => $transaction->getType(),
                default       => null,
            };

            if ($txValue === null) {
                return false;
            }

            $matches = match ($operator) {
                '='            => $txValue === $value,
                '!='           => $txValue !== $value,
                'contains'     => str_contains(strtolower($txValue), strtolower((string) $value)),
                'starts_with'  => str_starts_with(strtolower($txValue), strtolower((string) $value)),
                'ends_with'    => str_ends_with(strtolower($txValue), strtolower((string) $value)),
                '>'            => (float) $txValue > (float) $value,
                '>='           => (float) $txValue >= (float) $value,
                '<'            => (float) $txValue < (float) $value,
                '<='           => (float) $txValue <= (float) $value,
                default        => false,
            };

            if (! $matches) {
                return false;
            }
        }

        return true;
    }
}
