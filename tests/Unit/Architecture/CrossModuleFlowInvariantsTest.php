<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Architecture guardrail tests that lock the runtime sequence matrix documentation
 * to the actual source artifacts that implement each cross-module flow.
 *
 * These are STATIC analysis tests — no DB, no HTTP, no service container.
 * Each test reads source file contents and asserts structural invariants.
 *
 * Flows covered:
 *   1. Procure-to-Pay (P2P): PO → GRN → Invoice → Finance AP journal
 *   2. Order-to-Cash (O2C): Sales Order → Shipment → Invoice → Finance AR journal
 *   3. HR-to-Finance: PayrollRun → Approve → Payslip → (journal linkage contract)
 *   4. Cross-module tenant propagation in listener-driven paths
 */
class CrossModuleFlowInvariantsTest extends TestCase
{
    private string $repoRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repoRoot = dirname(__DIR__, 3);
    }

    // -------------------------------------------------------------------------
    // 1. Procure-to-Pay flow sequence invariants
    // -------------------------------------------------------------------------

    /**
     * P2P Step 1: ConfirmPurchaseOrderService must dispatch PurchaseOrderConfirmed.
     * P2P Step 2: PostGrnService must dispatch GoodsReceiptPosted.
     * P2P Step 3: ApprovePurchaseInvoiceService must dispatch PurchaseInvoiceApproved.
     * P2P Step 4: Inventory module must have a listener wired to GoodsReceiptPosted.
     * P2P Step 5: Finance module must have a listener wired to PurchaseInvoiceApproved.
     */
    public function test_p2p_flow_has_complete_event_chain_artifacts(): void
    {
        // --- Step 1: PO confirmation dispatches PurchaseOrderConfirmed --------
        $confirmService = $this->readSource(
            'app/Modules/Purchase/Application/Services/ConfirmPurchaseOrderService.php'
        );
        $this->assertStringContainsString(
            'PurchaseOrderConfirmed',
            $confirmService,
            'ConfirmPurchaseOrderService must reference PurchaseOrderConfirmed event'
        );
        $this->assertStringContainsString(
            'addEvent',
            $confirmService,
            'ConfirmPurchaseOrderService must dispatch the event via addEvent()'
        );

        // --- Step 2: GRN post dispatches GoodsReceiptPosted -------------------
        $postGrnService = $this->readSource(
            'app/Modules/Purchase/Application/Services/PostGrnService.php'
        );
        $this->assertStringContainsString(
            'GoodsReceiptPosted',
            $postGrnService,
            'PostGrnService must reference GoodsReceiptPosted event'
        );
        $this->assertStringContainsString(
            'addEvent',
            $postGrnService,
            'PostGrnService must dispatch the event via addEvent()'
        );

        // --- Step 3: Invoice approval dispatches PurchaseInvoiceApproved ------
        $approveInvoiceService = $this->readSource(
            'app/Modules/Purchase/Application/Services/ApprovePurchaseInvoiceService.php'
        );
        $this->assertStringContainsString(
            'PurchaseInvoiceApproved',
            $approveInvoiceService,
            'ApprovePurchaseInvoiceService must reference PurchaseInvoiceApproved event'
        );
        $this->assertStringContainsString(
            'addEvent',
            $approveInvoiceService,
            'ApprovePurchaseInvoiceService must dispatch the event via addEvent()'
        );

        // --- Step 4: Inventory listener wired to GoodsReceiptPosted -----------
        $inventoryProvider = $this->readSource(
            'app/Modules/Inventory/Infrastructure/Providers/InventoryServiceProvider.php'
        );
        $this->assertStringContainsString(
            'GoodsReceiptPosted',
            $inventoryProvider,
            'InventoryServiceProvider must register a listener for GoodsReceiptPosted'
        );
        $this->assertStringContainsString(
            'HandleGoodsReceiptPosted',
            $inventoryProvider,
            'InventoryServiceProvider must bind HandleGoodsReceiptPosted'
        );

        // Listener implementation file must exist
        $this->assertSourceExists(
            'app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php',
            'HandleGoodsReceiptPosted listener must exist in Inventory module'
        );

        // --- Step 5: Finance listener wired to PurchaseInvoiceApproved --------
        $financeProvider = $this->readSource(
            'app/Modules/Finance/Infrastructure/Providers/FinanceServiceProvider.php'
        );
        $this->assertStringContainsString(
            'PurchaseInvoiceApproved',
            $financeProvider,
            'FinanceServiceProvider must register a listener for PurchaseInvoiceApproved'
        );
        $this->assertStringContainsString(
            'HandlePurchaseInvoiceApproved',
            $financeProvider,
            'FinanceServiceProvider must bind HandlePurchaseInvoiceApproved'
        );

        // Listener implementation file must exist
        $this->assertSourceExists(
            'app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php',
            'HandlePurchaseInvoiceApproved listener must exist in Finance module'
        );
    }

    // -------------------------------------------------------------------------
    // 2. Order-to-Cash flow sequence invariants
    // -------------------------------------------------------------------------

    /**
     * O2C Step 1: ProcessShipmentService must dispatch ShipmentProcessed.
     * O2C Step 2: PostSalesInvoiceService must dispatch SalesInvoicePosted.
     * O2C Step 3: Finance module must have a listener wired to SalesInvoicePosted.
     */
    public function test_o2c_flow_has_complete_event_chain_artifacts(): void
    {
        // --- Step 1: Shipment processing dispatches ShipmentProcessed ---------
        $processShipmentService = $this->readSource(
            'app/Modules/Sales/Application/Services/ProcessShipmentService.php'
        );
        $this->assertStringContainsString(
            'ShipmentProcessed',
            $processShipmentService,
            'ProcessShipmentService must reference ShipmentProcessed event'
        );
        $this->assertStringContainsString(
            'addEvent',
            $processShipmentService,
            'ProcessShipmentService must dispatch the event via addEvent()'
        );

        // --- Step 2: Sales invoice posting dispatches SalesInvoicePosted ------
        $postInvoiceService = $this->readSource(
            'app/Modules/Sales/Application/Services/PostSalesInvoiceService.php'
        );
        $this->assertStringContainsString(
            'SalesInvoicePosted',
            $postInvoiceService,
            'PostSalesInvoiceService must reference SalesInvoicePosted event'
        );
        $this->assertStringContainsString(
            'addEvent',
            $postInvoiceService,
            'PostSalesInvoiceService must dispatch the event via addEvent()'
        );

        // --- Step 3: Finance listener wired to SalesInvoicePosted -------------
        $financeProvider = $this->readSource(
            'app/Modules/Finance/Infrastructure/Providers/FinanceServiceProvider.php'
        );
        $this->assertStringContainsString(
            'SalesInvoicePosted',
            $financeProvider,
            'FinanceServiceProvider must register a listener for SalesInvoicePosted'
        );
        $this->assertStringContainsString(
            'HandleSalesInvoicePosted',
            $financeProvider,
            'FinanceServiceProvider must bind HandleSalesInvoicePosted'
        );

        // Listener implementation file must exist
        $this->assertSourceExists(
            'app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php',
            'HandleSalesInvoicePosted listener must exist in Finance module'
        );
    }

    // -------------------------------------------------------------------------
    // 3. HR-to-Finance journal linkage contract
    // -------------------------------------------------------------------------

    /**
     * The HR-to-Finance posting path requires:
     *   a) PayrollRunApproved event is declared and dispatched by ApprovePayrollRunService.
     *   b) Payslip entity and model expose journal_entry_id (the FK to Finance journal).
     *   c) The hr_payslips migration declares the journal_entry_id FK to journal_entries.
     *   d) ProcessPayrollRunService initialises journalEntryId (even if null = open-debt marker).
     *
     * KNOWN DEBT: No Finance listener for PayrollRunApproved exists yet.
     * test_hr_to_finance_finance_listener_is_missing_known_debt() asserts this gap
     * is still present and must be removed when the listener is implemented.
     */
    public function test_hr_to_finance_journal_linkage_contract_artifacts_exist(): void
    {
        // a) PayrollRunApproved event declared ------------------------------------
        $this->assertSourceExists(
            'app/Modules/HR/Domain/Events/PayrollRunApproved.php',
            'PayrollRunApproved domain event must exist'
        );

        // ApprovePayrollRunService dispatches PayrollRunApproved
        $approveRunService = $this->readSource(
            'app/Modules/HR/Application/Services/ApprovePayrollRunService.php'
        );
        $this->assertStringContainsString(
            'PayrollRunApproved',
            $approveRunService,
            'ApprovePayrollRunService must reference PayrollRunApproved event'
        );
        $this->assertStringContainsString(
            'addEvent',
            $approveRunService,
            'ApprovePayrollRunService must dispatch PayrollRunApproved via addEvent()'
        );

        // b) Payslip entity exposes journal_entry_id getter ----------------------
        $payslipEntity = $this->readSource(
            'app/Modules/HR/Domain/Entities/Payslip.php'
        );
        $this->assertStringContainsString(
            'getJournalEntryId',
            $payslipEntity,
            'Payslip entity must expose getJournalEntryId() for Finance FK linkage'
        );
        $this->assertStringContainsString(
            'journalEntryId',
            $payslipEntity,
            'Payslip entity must hold journalEntryId property'
        );

        // b) PayslipModel includes journal_entry_id in fillable -----------------
        $payslipModel = $this->readSource(
            'app/Modules/HR/Infrastructure/Persistence/Eloquent/Models/PayslipModel.php'
        );
        $this->assertStringContainsString(
            'journal_entry_id',
            $payslipModel,
            'PayslipModel fillable must include journal_entry_id'
        );

        // c) Migration declares journal_entry_id FK to journal_entries ----------
        $payslipMigration = $this->readSource(
            'app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php'
        );
        $this->assertStringContainsString(
            'journal_entry_id',
            $payslipMigration,
            'hr_payslips migration must declare journal_entry_id column'
        );
        $this->assertStringContainsString(
            'journal_entries',
            $payslipMigration,
            'hr_payslips migration must declare FK to journal_entries table'
        );

        // d) ProcessPayrollRunService initialises journalEntryId ----------------
        $processRunService = $this->readSource(
            'app/Modules/HR/Application/Services/ProcessPayrollRunService.php'
        );
        $this->assertStringContainsString(
            'journalEntryId',
            $processRunService,
            'ProcessPayrollRunService must initialise journalEntryId when creating payslips'
        );
    }

    /**
     * Finance module must now have a listener wired to PayrollRunApproved,
     * closing the HR-to-Finance posting gap identified in the initial audit.
     *
     * @see docs/architecture/modules/hr.md  Section 10 — Known Debt (resolved)
     */
    public function test_hr_to_finance_finance_listener_gap_is_known_open_debt(): void
    {
        $financeProvider = $this->readSource(
            'app/Modules/Finance/Infrastructure/Providers/FinanceServiceProvider.php'
        );

        $this->assertStringContainsString(
            'PayrollRunApproved',
            $financeProvider,
            'FinanceServiceProvider must register a listener for PayrollRunApproved (HR-to-Finance posting)'
        );
        $this->assertStringContainsString(
            'HandlePayrollRunApproved',
            $financeProvider,
            'FinanceServiceProvider must bind HandlePayrollRunApproved listener'
        );

        $this->assertSourceExists(
            'app/Modules/Finance/Infrastructure/Listeners/HandlePayrollRunApproved.php',
            'HandlePayrollRunApproved listener must exist in Finance module'
        );
    }

    // -------------------------------------------------------------------------
    // 4. Cross-module tenant propagation in listener-driven paths
    // -------------------------------------------------------------------------

    /**
     * Every Finance listener that consumes cross-module events must propagate
     * tenantId from the event payload into the journal entry it creates.
     * This test asserts that the concrete listener implementations read $event->tenantId.
     */
    public function test_finance_listeners_propagate_tenant_id_from_event_payload(): void
    {
        $listeners = [
            'app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php',
            'app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php',
        ];

        foreach ($listeners as $listenerPath) {
            $contents = $this->readSource($listenerPath);
            $filename  = basename($listenerPath);

            $this->assertStringContainsString(
                'tenantId',
                $contents,
                "{$filename} must read tenantId from the event to propagate tenant context"
            );
        }
    }

    /**
     * The Inventory listener for GoodsReceiptPosted must propagate tenantId
     * into the StockMovement it creates to maintain tenant-scoped stock integrity.
     */
    public function test_inventory_listener_propagates_tenant_id_from_goods_receipt_event(): void
    {
        $listener = $this->readSource(
            'app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php'
        );

        $this->assertStringContainsString(
            'tenantId',
            $listener,
            'HandleGoodsReceiptPosted must propagate tenantId from GoodsReceiptPosted event into StockMovement'
        );
        $this->assertStringContainsString(
            'StockMovement',
            $listener,
            'HandleGoodsReceiptPosted must create a StockMovement entity'
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function readSource(string $relativePath): string
    {
        $absolutePath = $this->repoRoot . DIRECTORY_SEPARATOR . $relativePath;

        $this->assertFileExists(
            $absolutePath,
            "Expected source file not found: {$relativePath}"
        );

        $contents = file_get_contents($absolutePath);

        $this->assertNotFalse(
            $contents,
            "Could not read source file: {$relativePath}"
        );

        return (string) $contents;
    }

    private function assertSourceExists(string $relativePath, string $message = ''): void
    {
        $absolutePath = $this->repoRoot . DIRECTORY_SEPARATOR . $relativePath;
        $this->assertFileExists($absolutePath, $message ?: "Expected source file not found: {$relativePath}");
    }
}
