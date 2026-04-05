<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

// ─── Tenant Module ────────────────────────────────────────────────────────────
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Application\Services\TenantService;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

// ─── Auth Module ──────────────────────────────────────────────────────────────
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\Entities\Permission;
use Modules\Auth\Application\Services\UserService;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;

// ─── Configuration Module ─────────────────────────────────────────────────────
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Application\Services\OrgUnitService;
use Modules\Configuration\Application\Services\SettingService;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;

// ─── Accounting Module ────────────────────────────────────────────────────────
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\Entities\Payment;
use Modules\Accounting\Domain\Entities\Refund;
use Modules\Accounting\Application\Services\AccountService;
use Modules\Accounting\Application\Services\JournalEntryService;
use Modules\Accounting\Application\Services\PaymentService;
use Modules\Accounting\Application\Services\RefundService;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\RefundRepositoryInterface;

// ─── Core Exceptions ──────────────────────────────────────────────────────────
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class TenantAuthConfigAccountingTest extends TestCase
{
    // =========================================================================
    // TENANT MODULE TESTS
    // =========================================================================

    public function test_tenant_entity_creation(): void
    {
        $tenant = new Tenant(
            id: 'uuid-1',
            name: 'Acme Corp',
            slug: 'acme-corp',
            plan: 'enterprise',
            status: 'active',
            settings: ['timezone' => 'UTC'],
        );

        $this->assertSame('uuid-1', $tenant->getId());
        $this->assertSame('Acme Corp', $tenant->getName());
        $this->assertSame('acme-corp', $tenant->getSlug());
        $this->assertSame('enterprise', $tenant->getPlan());
        $this->assertSame('active', $tenant->getStatus());
        $this->assertSame(['timezone' => 'UTC'], $tenant->getSettings());
        $this->assertTrue($tenant->isActive());
        $this->assertFalse($tenant->isSuspended());
        $this->assertFalse($tenant->isTrial());
    }

    public function test_tenant_trial_status(): void
    {
        $tenant = new Tenant('uuid-2', 'Beta Ltd', 'beta', 'free', 'trial', []);
        $this->assertTrue($tenant->isTrial());
        $this->assertFalse($tenant->isActive());
    }

    public function test_tenant_suspended_status(): void
    {
        $tenant = new Tenant('uuid-3', 'Old Co', 'old-co', 'free', 'suspended', []);
        $this->assertTrue($tenant->isSuspended());
    }

    public function test_tenant_service_get_throws_when_not_found(): void
    {
        $repo = $this->createMock(TenantRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $service = new TenantService($repo);

        $this->expectException(NotFoundException::class);
        $service->getTenant('non-existent-id');
    }

    public function test_tenant_service_creates_with_default_status(): void
    {
        $expected = new Tenant('uuid-1', 'New Co', 'new-co', 'free', 'trial', []);

        $repo = $this->createMock(TenantRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('create')
            ->with($this->callback(fn($data) => $data['status'] === 'trial'))
            ->willReturn($expected);

        $service = new TenantService($repo);
        $result = $service->createTenant(['name' => 'New Co', 'slug' => 'new-co', 'plan' => 'free']);

        $this->assertSame('trial', $result->getStatus());
    }

    public function test_tenant_service_suspend_and_activate(): void
    {
        $active    = new Tenant('t1', 'Co', 'co', 'pro', 'active', []);
        $suspended = new Tenant('t1', 'Co', 'co', 'pro', 'suspended', []);

        $repo = $this->createMock(TenantRepositoryInterface::class);
        $repo->method('findById')->willReturn($active);
        $repo->method('update')
            ->willReturnCallback(fn($id, $data) => new Tenant('t1', 'Co', 'co', 'pro', $data['status'], []));

        $service = new TenantService($repo);

        $result = $service->suspendTenant('t1');
        $this->assertSame('suspended', $result->getStatus());

        $result = $service->activateTenant('t1');
        $this->assertSame('active', $result->getStatus());
    }

    // =========================================================================
    // AUTH MODULE TESTS
    // =========================================================================

    public function test_user_entity_creation(): void
    {
        $user = new User(
            id: 'user-1',
            tenantId: 'tenant-1',
            name: 'John Doe',
            email: 'john@example.com',
            role: 'admin',
            status: 'active',
            preferences: ['theme' => 'dark'],
            lastLoginAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->assertSame('user-1', $user->getId());
        $this->assertSame('tenant-1', $user->getTenantId());
        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('admin', $user->getRole());
        $this->assertTrue($user->isActive());
        $this->assertSame(['theme' => 'dark'], $user->getPreferences());
    }

    public function test_user_inactive_status(): void
    {
        $user = new User('u2', 't1', 'Jane', 'jane@x.com', 'user', 'inactive', []);
        $this->assertFalse($user->isActive());
    }

    public function test_role_entity_has_permission(): void
    {
        $role = new Role(
            id: 'role-1',
            name: 'Admin',
            slug: 'admin',
            permissions: ['users.create', 'users.delete', 'accounts.view'],
            tenantId: 'tenant-1',
        );

        $this->assertTrue($role->hasPermission('users.create'));
        $this->assertTrue($role->hasPermission('accounts.view'));
        $this->assertFalse($role->hasPermission('tenants.delete'));
    }

    public function test_role_wildcard_permission(): void
    {
        $role = new Role('r1', 'SuperAdmin', 'super-admin', ['*'], 'tenant-1');
        $this->assertTrue($role->hasPermission('anything.at.all'));
    }

    public function test_permission_entity(): void
    {
        $perm = new Permission('p1', 'Create Users', 'users.create', 'auth', 'Can create new users');
        $this->assertSame('p1', $perm->getId());
        $this->assertSame('auth', $perm->getModule());
        $this->assertSame('Can create new users', $perm->getDescription());
    }

    public function test_user_service_throws_not_found(): void
    {
        $repo = $this->createMock(UserRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $service = new UserService($repo);

        $this->expectException(NotFoundException::class);
        $service->getUser('non-existent');
    }

    public function test_user_service_rejects_duplicate_email(): void
    {
        $existing = new User('u1', 't1', 'Existing', 'dup@x.com', 'user', 'active', []);

        $repo = $this->createMock(UserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($existing);

        $service = new UserService($repo);

        $this->expectException(DomainException::class);
        $service->createUser(['email' => 'dup@x.com', 'tenant_id' => 't1', 'name' => 'Bob', 'password' => 'secret123']);
    }

    // =========================================================================
    // CONFIGURATION MODULE TESTS
    // =========================================================================

    public function test_setting_entity_casts_integer(): void
    {
        $setting = new Setting('s1', 't1', 'max_users', '50', 'integer', 'auth');
        $this->assertSame(50, $setting->getCastedValue());
        $this->assertSame('integer', $setting->getType());
    }

    public function test_setting_entity_casts_boolean(): void
    {
        $setting = new Setting('s2', 't1', 'feature_x', 'true', 'boolean');
        $this->assertTrue($setting->getCastedValue());
    }

    public function test_setting_entity_casts_json(): void
    {
        $setting = new Setting('s3', 't1', 'limits', '{"api":100}', 'json');
        $this->assertSame(['api' => 100], $setting->getCastedValue());
    }

    public function test_setting_entity_string_value(): void
    {
        $setting = new Setting('s4', 't1', 'timezone', 'UTC', 'string');
        $this->assertSame('UTC', $setting->getCastedValue());
    }

    public function test_org_unit_entity(): void
    {
        $unit = new OrgUnit('ou1', 't1', 'HQ', 'HQ', 'company', null, '/ou1/', 0, true, []);
        $this->assertTrue($unit->isRoot());
        $this->assertNull($unit->getParentId());
        $this->assertSame(0, $unit->getLevel());
    }

    public function test_org_unit_is_descendant_of(): void
    {
        $parent = new OrgUnit('ou1', 't1', 'HQ', 'HQ', 'company', null, '/ou1/', 0, true, []);
        $child  = new OrgUnit('ou2', 't1', 'HR', 'HR', 'department', 'ou1', '/ou1/ou2/', 1, true, []);

        $this->assertTrue($child->isDescendantOf('/ou1/'));
        $this->assertFalse($parent->isDescendantOf('/ou1/'));
    }

    public function test_org_unit_service_circular_ref_detection(): void
    {
        $parent = new OrgUnit('ou1', 't1', 'HQ', 'HQ', 'company', null, '/ou1/', 0, true, []);
        $child  = new OrgUnit('ou2', 't1', 'HR', 'HR', 'department', 'ou1', '/ou1/ou2/', 1, true, []);

        $repo = $this->createMock(OrgUnitRepositoryInterface::class);
        $repo->method('findById')->with('ou1')->willReturn($parent);
        $repo->method('getDescendants')->with('ou1')->willReturnCallback(
            fn () => collect([$child])
        );

        $service = new OrgUnitService($repo);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/circular/i');

        // Try to move parent (ou1) to be a child of its own descendant (ou2)
        $service->moveOrgUnit('ou1', 'ou2');
    }

    public function test_org_unit_service_cannot_move_to_self(): void
    {
        $unit = new OrgUnit('ou1', 't1', 'HQ', 'HQ', 'company', null, '/ou1/', 0, true, []);

        $repo = $this->createMock(OrgUnitRepositoryInterface::class);
        $repo->method('findById')->willReturn($unit);
        $repo->method('getDescendants')->willReturn(collect());

        $service = new OrgUnitService($repo);

        $this->expectException(DomainException::class);
        $service->moveOrgUnit('ou1', 'ou1');
    }

    public function test_setting_service_delegates_to_repo(): void
    {
        $expected = new Setting('s1', 't1', 'my_key', 'my_value', 'string');

        $repo = $this->createMock(SettingRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('set')
            ->with('my_key', 'my_value', 't1', 'string', null)
            ->willReturn($expected);

        $service = new SettingService($repo);
        $result  = $service->set('my_key', 'my_value', 't1');

        $this->assertSame('my_key', $result->getKey());
    }

    // =========================================================================
    // ACCOUNTING MODULE TESTS
    // =========================================================================

    public function test_account_entity(): void
    {
        $account = new Account(
            id: 'acc-1',
            tenantId: 't1',
            code: '1000',
            name: 'Cash',
            type: 'asset',
            parentId: null,
            isActive: true,
            openingBalance: 0.0,
            currentBalance: 5000.0,
            currency: 'USD',
            description: 'Main cash account',
        );

        $this->assertSame('1000', $account->getCode());
        $this->assertSame('asset', $account->getType());
        $this->assertTrue($account->isDebitNormal());
        $this->assertFalse($account->isCreditNormal());
        $this->assertSame(5000.0, $account->getCurrentBalance());
    }

    public function test_account_income_is_credit_normal(): void
    {
        $account = new Account('a2', 't1', '4000', 'Revenue', 'income', null, true, 0.0, 0.0, 'USD', null);
        $this->assertTrue($account->isCreditNormal());
        $this->assertFalse($account->isDebitNormal());
    }

    public function test_journal_entry_is_balanced(): void
    {
        $entry = new JournalEntry(
            id: 'je-1', tenantId: 't1', entryNumber: 'JE-000001',
            date: new DateTimeImmutable('2024-01-15'),
            description: 'Test entry', reference: null,
            status: 'draft', totalDebit: 1000.0, totalCredit: 1000.0,
            createdBy: null,
        );

        $this->assertTrue($entry->isBalanced());
        $this->assertTrue($entry->isDraft());
        $this->assertFalse($entry->isPosted());
    }

    public function test_journal_entry_unbalanced(): void
    {
        $entry = new JournalEntry(
            id: 'je-2', tenantId: 't1', entryNumber: 'JE-000002',
            date: new DateTimeImmutable(), description: 'Unbalanced',
            reference: null, status: 'draft',
            totalDebit: 1000.0, totalCredit: 900.0, createdBy: null,
        );

        $this->assertFalse($entry->isBalanced());
    }

    public function test_journal_entry_service_rejects_unbalanced_lines(): void
    {
        $repo = $this->createMock(JournalEntryRepositoryInterface::class);
        $repo->method('nextEntryNumber')->willReturn('JE-000001');

        $service = new JournalEntryService($repo);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/not balanced/i');

        $service->createEntry(
            ['tenant_id' => 't1', 'date' => '2024-01-01', 'description' => 'Test'],
            [
                ['account_id' => 'acc-1', 'debit' => 1000, 'credit' => 0],
                ['account_id' => 'acc-2', 'debit' => 0,    'credit' => 500], // unbalanced
            ]
        );
    }

    public function test_journal_entry_service_requires_two_lines(): void
    {
        $repo = $this->createMock(JournalEntryRepositoryInterface::class);
        $service = new JournalEntryService($repo);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/at least 2 lines/i');

        $service->createEntry(
            ['tenant_id' => 't1', 'date' => '2024-01-01', 'description' => 'Test'],
            [['account_id' => 'acc-1', 'debit' => 100, 'credit' => 0]]
        );
    }

    public function test_journal_entry_service_post_fails_if_not_draft(): void
    {
        $posted = new JournalEntry('je-1', 't1', 'JE-000001',
            new DateTimeImmutable(), 'Posted', null, 'posted', 1000.0, 1000.0, null);

        $repo = $this->createMock(JournalEntryRepositoryInterface::class);
        $repo->method('findById')->willReturn($posted);

        $service = new JournalEntryService($repo);

        $this->expectException(DomainException::class);
        $service->postEntry('je-1');
    }

    public function test_journal_entry_service_void(): void
    {
        $draft = new JournalEntry('je-1', 't1', 'JE-000001',
            new DateTimeImmutable(), 'Draft', null, 'draft', 500.0, 500.0, null);
        $voided = new JournalEntry('je-1', 't1', 'JE-000001',
            new DateTimeImmutable(), 'Draft', null, 'voided', 500.0, 500.0, null);

        $repo = $this->createMock(JournalEntryRepositoryInterface::class);
        $repo->method('findById')->willReturn($draft);
        $repo->method('updateStatus')->with('je-1', 'voided')->willReturn($voided);

        $service = new JournalEntryService($repo);
        $result = $service->voidEntry('je-1');

        $this->assertTrue($result->isVoided());
    }

    public function test_transaction_rule_matches_by_description(): void
    {
        $rule = new TransactionRule(
            id: 'rule-1', tenantId: 't1', name: 'Office Supplies',
            conditions: [['field' => 'description', 'operator' => 'contains', 'value' => 'Office']],
            categoryId: 'cat-1', accountId: null, applyTo: 'all', priority: 1, isActive: true,
        );

        $matching = new BankTransaction(
            id: 'bt1', tenantId: 't1', bankAccountId: 'ba1',
            date: new DateTimeImmutable(), description: 'Purchase: Office Depot',
            amount: 50.0, type: 'debit', status: 'pending', source: 'import',
            categoryId: null, journalEntryId: null, reference: null, metadata: null,
        );

        $nonMatching = new BankTransaction(
            id: 'bt2', tenantId: 't1', bankAccountId: 'ba1',
            date: new DateTimeImmutable(), description: 'Restaurant dinner',
            amount: 30.0, type: 'debit', status: 'pending', source: 'import',
            categoryId: null, journalEntryId: null, reference: null, metadata: null,
        );

        $this->assertTrue($rule->matches($matching));
        $this->assertFalse($rule->matches($nonMatching));
    }

    public function test_transaction_rule_apply_to_debit_only(): void
    {
        $rule = new TransactionRule(
            id: 'rule-2', tenantId: 't1', name: 'Credit Only Rule',
            conditions: [], categoryId: 'cat-1', accountId: null,
            applyTo: 'credit', priority: 0, isActive: true,
        );

        $debit = new BankTransaction(
            id: 'bt3', tenantId: 't1', bankAccountId: 'ba1',
            date: new DateTimeImmutable(), description: 'Test', amount: 100.0,
            type: 'debit', status: 'pending', source: 'manual',
            categoryId: null, journalEntryId: null, reference: null, metadata: null,
        );

        $credit = new BankTransaction(
            id: 'bt4', tenantId: 't1', bankAccountId: 'ba1',
            date: new DateTimeImmutable(), description: 'Test', amount: 100.0,
            type: 'credit', status: 'pending', source: 'manual',
            categoryId: null, journalEntryId: null, reference: null, metadata: null,
        );

        $this->assertFalse($rule->matches($debit));
        $this->assertTrue($rule->matches($credit));
    }

    public function test_transaction_rule_inactive_never_matches(): void
    {
        $rule = new TransactionRule(
            id: 'rule-3', tenantId: 't1', name: 'Inactive',
            conditions: [], categoryId: null, accountId: null,
            applyTo: 'all', priority: 0, isActive: false,
        );

        $tx = new BankTransaction(
            id: 'bt5', tenantId: 't1', bankAccountId: 'ba1',
            date: new DateTimeImmutable(), description: 'Anything', amount: 10.0,
            type: 'credit', status: 'pending', source: 'manual',
            categoryId: null, journalEntryId: null, reference: null, metadata: null,
        );

        $this->assertFalse($rule->matches($tx));
    }

    public function test_payment_entity(): void
    {
        $payment = new Payment(
            id: 'pay-1', tenantId: 't1', paymentNumber: 'PAY-000001',
            paymentDate: new DateTimeImmutable('2024-03-01'),
            amount: 2500.0, currency: 'USD', paymentMethod: 'bank_transfer',
            fromAccountId: 'acc-1', toAccountId: 'acc-2',
            reference: 'INV-001', notes: null, status: 'pending',
            journalEntryId: null,
        );

        $this->assertSame('PAY-000001', $payment->getPaymentNumber());
        $this->assertSame(2500.0, $payment->getAmount());
        $this->assertSame('bank_transfer', $payment->getPaymentMethod());
    }

    public function test_refund_entity(): void
    {
        $refund = new Refund(
            id: 'ref-1', tenantId: 't1', refundNumber: 'REF-000001',
            refundDate: new DateTimeImmutable('2024-03-10'),
            amount: 100.0, currency: 'USD', paymentMethod: 'cash',
            accountId: 'acc-1', reference: null, notes: 'Partial refund',
            status: 'pending', originalPaymentId: 'pay-1',
        );

        $this->assertSame('REF-000001', $refund->getRefundNumber());
        $this->assertSame(100.0, $refund->getAmount());
        $this->assertSame('pay-1', $refund->getOriginalPaymentId());
    }

    public function test_payment_service_assigns_payment_number(): void
    {
        $expected = new Payment(
            'pay-1', 't1', 'PAY-000001', new DateTimeImmutable('2024-01-01'),
            1000.0, 'USD', 'cash', null, null, null, null, 'pending', null
        );

        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $repo->method('nextPaymentNumber')->willReturn('PAY-000001');
        $repo->method('create')->willReturn($expected);

        $service = new PaymentService($repo);
        $result = $service->createPayment(['tenant_id' => 't1', 'payment_date' => '2024-01-01', 'amount' => 1000, 'payment_method' => 'cash']);

        $this->assertSame('PAY-000001', $result->getPaymentNumber());
    }

    public function test_refund_service_rejects_amount_exceeding_payment(): void
    {
        $original = new Payment(
            'pay-1', 't1', 'PAY-000001', new DateTimeImmutable(),
            100.0, 'USD', 'cash', null, null, null, null, 'completed', null
        );

        $payRepo = $this->createMock(PaymentRepositoryInterface::class);
        $payRepo->method('findById')->willReturn($original);

        $refundRepo = $this->createMock(RefundRepositoryInterface::class);
        $refundRepo->method('nextRefundNumber')->willReturn('REF-000001');

        $service = new RefundService($refundRepo, $payRepo);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/cannot exceed/i');

        $service->createRefund([
            'tenant_id'          => 't1',
            'refund_date'        => '2024-01-02',
            'amount'             => 200.0,  // exceeds 100
            'payment_method'     => 'cash',
            'original_payment_id'=> 'pay-1',
        ]);
    }

    public function test_account_service_rejects_duplicate_code(): void
    {
        $existing = new Account('acc-1', 't1', '1000', 'Cash', 'asset', null, true, 0.0, 0.0, 'USD', null);

        $repo = $this->createMock(AccountRepositoryInterface::class);
        $repo->method('findByCode')->willReturn($existing);

        $service = new AccountService($repo);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/already exists/i');

        $service->createAccount(['tenant_id' => 't1', 'code' => '1000', 'name' => 'Cash2', 'type' => 'asset']);
    }
}
