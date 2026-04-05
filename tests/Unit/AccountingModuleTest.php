<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\Entities\BankAccount;
use Modules\Accounting\Domain\Entities\Payment;
use PHPUnit\Framework\TestCase;
class AccountingModuleTest extends TestCase {
    public function test_account_entity(): void {
        $a = new Account(1, 1, '1000', 'Cash', 'asset', 'current_asset', null, true, 'debit', 'Main cash account');
        $this->assertSame('1000', $a->getCode());
        $this->assertSame('asset', $a->getType());
        $this->assertSame('debit', $a->getNormalBalance());
    }
    public function test_journal_line_rejects_both_debit_and_credit(): void {
        $this->expectException(\InvalidArgumentException::class);
        new JournalLine(null, 1, 1, 100.0, 50.0, null);
    }
    public function test_journal_entry_balanced(): void {
        $lines = [
            new JournalLine(null, 0, 1, 100.0, 0.0, null),
            new JournalLine(null, 0, 2, 0.0, 100.0, null),
        ];
        $entry = new JournalEntry(1, 1, 'JE001', 'Test', new \DateTimeImmutable(), 'draft', 'USD', null, null, $lines);
        $this->assertTrue($entry->isBalanced());
    }
    public function test_journal_entry_unbalanced(): void {
        $lines = [
            new JournalLine(null, 0, 1, 100.0, 0.0, null),
            new JournalLine(null, 0, 2, 0.0, 90.0, null),
        ];
        $entry = new JournalEntry(1, 1, 'JE002', 'Test', new \DateTimeImmutable(), 'draft', 'USD', null, null, $lines);
        $this->assertFalse($entry->isBalanced());
    }
    public function test_bank_transaction_is_categorized(): void {
        $txn = new BankTransaction(1, 1, 1, 'debit', 500.0, new \DateTimeImmutable(), 'Office supplies', 'categorized', 'manual', 5, null);
        $this->assertTrue($txn->isCategorized());
    }
    public function test_transaction_rule_matches(): void {
        $rule = new TransactionRule(1, 1, 'Supplies rule', 'debit', 'description', 'Office', 5, 1);
        $txn = new BankTransaction(1, 1, 1, 'debit', 200.0, new \DateTimeImmutable(), 'Office supplies purchase', 'pending', 'manual', null, null);
        $this->assertTrue($rule->matches($txn));
    }
    public function test_transaction_rule_no_match_wrong_type(): void {
        $rule = new TransactionRule(1, 1, 'Rule', 'credit', 'description', 'Office', 5, 1);
        $txn = new BankTransaction(1, 1, 1, 'debit', 200.0, new \DateTimeImmutable(), 'Office supplies', 'pending', 'manual', null, null);
        $this->assertFalse($rule->matches($txn));
    }
    public function test_bank_account_entity(): void {
        $ba = new BankAccount(1, 1, 'Main Checking', 'checking', 'USD', 101, 5000.0, true);
        $this->assertSame('checking', $ba->getAccountType());
        $this->assertEqualsWithDelta(5000.0, $ba->getOpeningBalance(), 0.001);
    }
    public function test_payment_entity(): void {
        $p = new Payment(1, 1, 'receivable', 42, 1500.0, 'USD', new \DateTimeImmutable(), 'bank', 'REF001', 'confirmed');
        $this->assertSame('receivable', $p->getType());
        $this->assertEqualsWithDelta(1500.0, $p->getAmount(), 0.001);
    }
}
