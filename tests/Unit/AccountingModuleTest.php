<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Entities\JournalEntry;

class AccountingModuleTest extends TestCase
{
    private function makeAccount(array $overrides = []): Account
    {
        return new Account(
            $overrides['id'] ?? 1, $overrides['tenant_id'] ?? 1,
            $overrides['code'] ?? '1000', $overrides['name'] ?? 'Cash',
            $overrides['type'] ?? 'asset', 'current', null,
            $overrides['balance'] ?? 0.0, 'USD', true, null, null, null
        );
    }
    private function makeJournalEntry(string $status = 'draft', float $debit = 100.0, float $credit = 100.0): JournalEntry
    {
        return new JournalEntry(
            1, 1, 'JE-001', $status, 'Test Entry', 'USD',
            $debit, $credit, null, null, [], null, null, null
        );
    }

    public function test_account_creation(): void
    {
        $acc = $this->makeAccount();
        $this->assertEquals('1000', $acc->getCode());
        $this->assertEquals('asset', $acc->getType());
        $this->assertEquals(0.0, $acc->getBalance());
        $this->assertTrue($acc->isActive());
    }

    public function test_account_debit(): void
    {
        $acc = $this->makeAccount(['balance' => 500.0]);
        $acc->debit(200.0);
        $this->assertEquals(700.0, $acc->getBalance());
    }

    public function test_account_credit(): void
    {
        $acc = $this->makeAccount(['balance' => 500.0]);
        $acc->credit(100.0);
        $this->assertEquals(400.0, $acc->getBalance());
    }

    public function test_journal_entry_is_balanced(): void
    {
        $je = $this->makeJournalEntry('draft', 100.0, 100.0);
        $this->assertTrue($je->isBalanced());
    }

    public function test_journal_entry_is_not_balanced(): void
    {
        $je = $this->makeJournalEntry('draft', 100.0, 90.0);
        $this->assertFalse($je->isBalanced());
    }

    public function test_journal_entry_post(): void
    {
        $je = $this->makeJournalEntry('draft', 100.0, 100.0);
        $je->post();
        $this->assertEquals('posted', $je->getStatus());
        $this->assertNotNull($je->getPostedAt());
    }

    public function test_journal_entry_post_fails_if_not_draft(): void
    {
        $je = $this->makeJournalEntry('posted', 100.0, 100.0);
        $this->expectException(\DomainException::class);
        $je->post();
    }

    public function test_journal_entry_post_fails_if_unbalanced(): void
    {
        $je = $this->makeJournalEntry('draft', 100.0, 90.0);
        $this->expectException(\DomainException::class);
        $je->post();
    }
}
