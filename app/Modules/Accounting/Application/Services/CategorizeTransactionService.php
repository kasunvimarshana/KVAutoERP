<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\CategorizeTransactionServiceInterface;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class CategorizeTransactionService implements CategorizeTransactionServiceInterface
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $transactionRepository,
        private readonly TransactionRuleRepositoryInterface $ruleRepository,
    ) {}

    public function categorize(int $bankTransactionId, int $accountId): BankTransaction
    {
        $transaction = $this->transactionRepository->findById($bankTransactionId);

        if ($transaction === null) {
            throw new NotFoundException("Bank transaction #{$bankTransactionId} not found.");
        }

        $updated = $this->transactionRepository->update($bankTransactionId, [
            'account_id' => $accountId,
            'status'     => BankTransaction::STATUS_CATEGORIZED,
        ]);

        return $updated;
    }

    public function autoCategorize(int $bankTransactionId): ?BankTransaction
    {
        $transaction = $this->transactionRepository->findById($bankTransactionId);

        if ($transaction === null) {
            throw new NotFoundException("Bank transaction #{$bankTransactionId} not found.");
        }

        $rules = $this->ruleRepository->findActive($transaction->tenantId);

        foreach ($rules as $rule) {
            if ($this->matchesRule($transaction, $rule)) {
                return $this->categorize($bankTransactionId, $rule->accountId);
            }
        }

        return null;
    }

    public function matchesRule(BankTransaction $transaction, TransactionRule $rule): bool
    {
        // Filter by apply_to scope first
        if ($rule->applyTo !== TransactionRule::APPLY_TO_ALL) {
            if ($rule->applyTo === TransactionRule::APPLY_TO_DEBIT && $transaction->type !== BankTransaction::TYPE_DEBIT) {
                return false;
            }

            if ($rule->applyTo === TransactionRule::APPLY_TO_CREDIT && $transaction->type !== BankTransaction::TYPE_CREDIT) {
                return false;
            }
        }

        foreach ($rule->conditions as $condition) {
            if (!$this->evaluateCondition($transaction, $condition)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param array{field: string, operator: string, value: string} $condition
     */
    private function evaluateCondition(BankTransaction $transaction, array $condition): bool
    {
        $field    = $condition['field']    ?? '';
        $operator = $condition['operator'] ?? 'contains';
        $value    = $condition['value']    ?? '';

        $actual = match ($field) {
            'description' => $transaction->description,
            'amount'      => (string) $transaction->amount,
            'reference'   => (string) ($transaction->reference ?? ''),
            default       => '',
        };

        return match ($operator) {
            'contains'       => str_contains(strtolower($actual), strtolower($value)),
            'not_contains'   => !str_contains(strtolower($actual), strtolower($value)),
            'equals'         => strtolower($actual) === strtolower($value),
            'not_equals'     => strtolower($actual) !== strtolower($value),
            'starts_with'    => str_starts_with(strtolower($actual), strtolower($value)),
            'ends_with'      => str_ends_with(strtolower($actual), strtolower($value)),
            'greater_than'   => (float) $actual > (float) $value,
            'less_than'      => (float) $actual < (float) $value,
            default          => false,
        };
    }
}
