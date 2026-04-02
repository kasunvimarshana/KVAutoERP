<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

// ── Returns — Value Objects ──────────────────────────────────────────────────
use Modules\Returns\Domain\ValueObjects\ReturnType;
use Modules\Returns\Domain\ValueObjects\ReturnStatus;
use Modules\Returns\Domain\ValueObjects\ReturnCondition;
use Modules\Returns\Domain\ValueObjects\ReturnDisposition;

// ── Returns — Entities ───────────────────────────────────────────────────────
use Modules\Returns\Domain\Entities\StockReturn;
use Modules\Returns\Domain\Entities\StockReturnLine;

// ── Returns — Domain Events ──────────────────────────────────────────────────
use Modules\Returns\Domain\Events\StockReturnCreated;
use Modules\Returns\Domain\Events\StockReturnUpdated;
use Modules\Returns\Domain\Events\StockReturnDeleted;
use Modules\Returns\Domain\Events\StockReturnApproved;
use Modules\Returns\Domain\Events\StockReturnRejected;
use Modules\Returns\Domain\Events\StockReturnCompleted;
use Modules\Returns\Domain\Events\StockReturnCancelled;
use Modules\Returns\Domain\Events\StockReturnCreditMemoIssued;
use Modules\Returns\Domain\Events\StockReturnInventoryAdjusted;
use Modules\Returns\Domain\Events\StockReturnLineCreated;
use Modules\Returns\Domain\Events\StockReturnLineUpdated;
use Modules\Returns\Domain\Events\StockReturnLineDeleted;
use Modules\Returns\Domain\Events\StockReturnLinePassed;
use Modules\Returns\Domain\Events\StockReturnLineFailed;

// ── Returns — Exceptions ─────────────────────────────────────────────────────
use Modules\Returns\Domain\Exceptions\StockReturnNotFoundException;
use Modules\Returns\Domain\Exceptions\StockReturnLineNotFoundException;

// ── Returns — Repository Interfaces ─────────────────────────────────────────
use Modules\Returns\Domain\RepositoryInterfaces\StockReturnRepositoryInterface;
use Modules\Returns\Domain\RepositoryInterfaces\StockReturnLineRepositoryInterface;

// ── Returns — DTOs ───────────────────────────────────────────────────────────
use Modules\Returns\Application\DTOs\StockReturnData;
use Modules\Returns\Application\DTOs\UpdateStockReturnData;
use Modules\Returns\Application\DTOs\StockReturnLineData;
use Modules\Returns\Application\DTOs\UpdateStockReturnLineData;

// ── Returns — Service Interfaces ─────────────────────────────────────────────
use Modules\Returns\Application\Contracts\CreateStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\FindStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\UpdateStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\DeleteStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\ApproveStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\RejectStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\CompleteStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\CancelStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\IssueCreditMemoServiceInterface;
use Modules\Returns\Application\Contracts\ProcessReturnInventoryAdjustmentServiceInterface;
use Modules\Returns\Application\Contracts\CreateStockReturnLineServiceInterface;
use Modules\Returns\Application\Contracts\FindStockReturnLineServiceInterface;
use Modules\Returns\Application\Contracts\UpdateStockReturnLineServiceInterface;
use Modules\Returns\Application\Contracts\DeleteStockReturnLineServiceInterface;

// ── Returns — Services ───────────────────────────────────────────────────────
use Modules\Returns\Application\Services\CreateStockReturnService;
use Modules\Returns\Application\Services\FindStockReturnService;
use Modules\Returns\Application\Services\UpdateStockReturnService;
use Modules\Returns\Application\Services\DeleteStockReturnService;
use Modules\Returns\Application\Services\ApproveStockReturnService;
use Modules\Returns\Application\Services\RejectStockReturnService;
use Modules\Returns\Application\Services\CompleteStockReturnService;
use Modules\Returns\Application\Services\CancelStockReturnService;
use Modules\Returns\Application\Services\IssueCreditMemoService;
use Modules\Returns\Application\Services\ProcessReturnInventoryAdjustmentService;
use Modules\Returns\Application\Services\CreateStockReturnLineService;
use Modules\Returns\Application\Services\FindStockReturnLineService;
use Modules\Returns\Application\Services\UpdateStockReturnLineService;
use Modules\Returns\Application\Services\DeleteStockReturnLineService;

// ── Returns — Infrastructure ─────────────────────────────────────────────────
use Modules\Returns\Infrastructure\Persistence\Eloquent\Models\StockReturnModel;
use Modules\Returns\Infrastructure\Persistence\Eloquent\Models\StockReturnLineModel;
use Modules\Returns\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockReturnRepository;
use Modules\Returns\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockReturnLineRepository;
use Modules\Returns\Infrastructure\Http\Controllers\StockReturnController;
use Modules\Returns\Infrastructure\Http\Controllers\StockReturnLineController;
use Modules\Returns\Infrastructure\Http\Requests\StoreStockReturnRequest;
use Modules\Returns\Infrastructure\Http\Requests\UpdateStockReturnRequest;
use Modules\Returns\Infrastructure\Http\Requests\StoreStockReturnLineRequest;
use Modules\Returns\Infrastructure\Http\Requests\UpdateStockReturnLineRequest;
use Modules\Returns\Infrastructure\Http\Resources\StockReturnResource;
use Modules\Returns\Infrastructure\Http\Resources\StockReturnCollection;
use Modules\Returns\Infrastructure\Http\Resources\StockReturnLineResource;
use Modules\Returns\Infrastructure\Http\Resources\StockReturnLineCollection;
use Modules\Returns\Infrastructure\Providers\ReturnsServiceProvider;

// ── Core ─────────────────────────────────────────────────────────────────────
use Modules\Core\Domain\Events\BaseEvent;
use Modules\Core\Application\DTOs\BaseDto;
use Modules\Core\Application\Services\BaseService;
use Modules\Core\Application\Contracts\WriteServiceInterface;
use Modules\Core\Application\Contracts\ReadServiceInterface;

/**
 * WIMSReturnsModuleTest
 *
 * Validates the Returns Management System module which implements both
 * purchase returns (to suppliers) and sales returns (from customers)
 * within the enterprise Warehouse & Inventory Management System (WIMS).
 *
 * Covers: partial returns, batch/lot/serial tracking, restocking,
 * quality checks, restocking fees, condition-based handling (good/damaged),
 * credit memos, inventory valuation layer adjustments, and full audit trails.
 */
class WIMSReturnsModuleTest extends TestCase
{
    // ========================================================================
    // RETURN TYPE — VALUE OBJECT
    // ========================================================================

    public function test_return_type_purchase_return_constant(): void
    {
        $this->assertSame('purchase_return', ReturnType::PURCHASE_RETURN);
    }

    public function test_return_type_sales_return_constant(): void
    {
        $this->assertSame('sales_return', ReturnType::SALES_RETURN);
    }

    public function test_return_type_values_contains_all(): void
    {
        $values = ReturnType::values();
        $this->assertContains('purchase_return', $values);
        $this->assertContains('sales_return', $values);
        $this->assertCount(2, $values);
    }

    // ========================================================================
    // RETURN STATUS — VALUE OBJECT
    // ========================================================================

    public function test_return_status_draft_constant(): void
    {
        $this->assertSame('draft', ReturnStatus::DRAFT);
    }

    public function test_return_status_pending_inspection_constant(): void
    {
        $this->assertSame('pending_inspection', ReturnStatus::PENDING_INSPECTION);
    }

    public function test_return_status_approved_constant(): void
    {
        $this->assertSame('approved', ReturnStatus::APPROVED);
    }

    public function test_return_status_rejected_constant(): void
    {
        $this->assertSame('rejected', ReturnStatus::REJECTED);
    }

    public function test_return_status_completed_constant(): void
    {
        $this->assertSame('completed', ReturnStatus::COMPLETED);
    }

    public function test_return_status_cancelled_constant(): void
    {
        $this->assertSame('cancelled', ReturnStatus::CANCELLED);
    }

    public function test_return_status_values_contains_all(): void
    {
        $values = ReturnStatus::values();
        $this->assertContains('draft', $values);
        $this->assertContains('pending_inspection', $values);
        $this->assertContains('approved', $values);
        $this->assertContains('rejected', $values);
        $this->assertContains('completed', $values);
        $this->assertContains('cancelled', $values);
        $this->assertCount(6, $values);
    }

    // ========================================================================
    // RETURN CONDITION — VALUE OBJECT
    // ========================================================================

    public function test_return_condition_good_constant(): void
    {
        $this->assertSame('good', ReturnCondition::GOOD);
    }

    public function test_return_condition_damaged_constant(): void
    {
        $this->assertSame('damaged', ReturnCondition::DAMAGED);
    }

    public function test_return_condition_defective_constant(): void
    {
        $this->assertSame('defective', ReturnCondition::DEFECTIVE);
    }

    public function test_return_condition_expired_constant(): void
    {
        $this->assertSame('expired', ReturnCondition::EXPIRED);
    }

    public function test_return_condition_values_contains_all(): void
    {
        $values = ReturnCondition::values();
        $this->assertContains('good', $values);
        $this->assertContains('damaged', $values);
        $this->assertContains('defective', $values);
        $this->assertContains('expired', $values);
        $this->assertCount(4, $values);
    }

    // ========================================================================
    // RETURN DISPOSITION — VALUE OBJECT
    // ========================================================================

    public function test_return_disposition_restock_constant(): void
    {
        $this->assertSame('restock', ReturnDisposition::RESTOCK);
    }

    public function test_return_disposition_scrap_constant(): void
    {
        $this->assertSame('scrap', ReturnDisposition::SCRAP);
    }

    public function test_return_disposition_vendor_return_constant(): void
    {
        $this->assertSame('vendor_return', ReturnDisposition::VENDOR_RETURN);
    }

    public function test_return_disposition_quarantine_constant(): void
    {
        $this->assertSame('quarantine', ReturnDisposition::QUARANTINE);
    }

    public function test_return_disposition_values_contains_all(): void
    {
        $values = ReturnDisposition::values();
        $this->assertContains('restock', $values);
        $this->assertContains('scrap', $values);
        $this->assertContains('vendor_return', $values);
        $this->assertContains('quarantine', $values);
        $this->assertCount(4, $values);
    }

    // ========================================================================
    // STOCK RETURN ENTITY — DEFAULTS
    // ========================================================================

    public function test_stock_return_entity_defaults_for_purchase_return(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-PO-001',
            returnType: 'purchase_return',
            partyId: 10,
            partyType: 'supplier',
        );

        $this->assertEquals(1, $return->getTenantId());
        $this->assertEquals('RET-PO-001', $return->getReferenceNumber());
        $this->assertEquals('purchase_return', $return->getReturnType());
        $this->assertEquals(10, $return->getPartyId());
        $this->assertEquals('supplier', $return->getPartyType());
        $this->assertEquals('draft', $return->getStatus());
        $this->assertTrue($return->isPurchaseReturn());
        $this->assertFalse($return->isSalesReturn());
        $this->assertEquals(0.0, $return->getTotalAmount());
        $this->assertEquals('USD', $return->getCurrency());
        $this->assertTrue($return->getRestock());
        $this->assertEquals(0.0, $return->getRestockingFee());
        $this->assertFalse($return->getCreditMemoIssued());
        $this->assertNull($return->getCreditMemoReference());
        $this->assertNull($return->getId());
        $this->assertNull($return->getOriginalReferenceId());
        $this->assertNull($return->getOriginalReferenceType());
        $this->assertNull($return->getReturnReason());
        $this->assertNull($return->getApprovedBy());
        $this->assertNull($return->getApprovedAt());
        $this->assertNull($return->getProcessedBy());
        $this->assertNull($return->getProcessedAt());
        $this->assertNull($return->getNotes());
        $this->assertNull($return->getRestockLocationId());
    }

    public function test_stock_return_entity_defaults_for_sales_return(): void
    {
        $return = new StockReturn(
            tenantId: 2,
            referenceNumber: 'RET-SO-001',
            returnType: 'sales_return',
            partyId: 20,
            partyType: 'customer',
        );

        $this->assertEquals('sales_return', $return->getReturnType());
        $this->assertEquals('customer', $return->getPartyType());
        $this->assertFalse($return->isPurchaseReturn());
        $this->assertTrue($return->isSalesReturn());
    }

    public function test_stock_return_entity_with_all_fields(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-FULL-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
            originalReferenceId: 100,
            originalReferenceType: 'purchase_order',
            returnReason: 'Damaged goods',
            totalAmount: 500.00,
            currency: 'EUR',
            restock: false,
            restockLocationId: 3,
            restockingFee: 25.00,
            creditMemoIssued: false,
            creditMemoReference: null,
            approvedBy: null,
            approvedAt: null,
            processedBy: null,
            processedAt: null,
            notes: 'Items received damaged',
            status: 'draft',
            id: 42,
        );

        $this->assertEquals(42, $return->getId());
        $this->assertEquals(100, $return->getOriginalReferenceId());
        $this->assertEquals('purchase_order', $return->getOriginalReferenceType());
        $this->assertEquals('Damaged goods', $return->getReturnReason());
        $this->assertEquals(500.00, $return->getTotalAmount());
        $this->assertEquals('EUR', $return->getCurrency());
        $this->assertFalse($return->getRestock());
        $this->assertEquals(3, $return->getRestockLocationId());
        $this->assertEquals(25.00, $return->getRestockingFee());
        $this->assertEquals('Items received damaged', $return->getNotes());
    }

    public function test_stock_return_timestamps_set_by_default(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-TIME-001',
            returnType: 'sales_return',
            partyId: 5,
            partyType: 'customer',
        );

        $this->assertInstanceOf(\DateTimeInterface::class, $return->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $return->getUpdatedAt());
    }

    // ========================================================================
    // STOCK RETURN ENTITY — STATE TRANSITIONS
    // ========================================================================

    public function test_stock_return_approve_transition(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-APP-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );

        $return->approve(99);

        $this->assertEquals('approved', $return->getStatus());
        $this->assertEquals(99, $return->getApprovedBy());
        $this->assertInstanceOf(\DateTimeInterface::class, $return->getApprovedAt());
    }

    public function test_stock_return_reject_transition(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-REJ-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );

        $return->reject();

        $this->assertEquals('rejected', $return->getStatus());
    }

    public function test_stock_return_complete_transition(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-COMP-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );

        $return->approve(10);
        $return->complete(7);

        $this->assertEquals('completed', $return->getStatus());
        $this->assertEquals(7, $return->getProcessedBy());
        $this->assertInstanceOf(\DateTimeInterface::class, $return->getProcessedAt());
    }

    public function test_stock_return_cancel_transition(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-CAN-001',
            returnType: 'sales_return',
            partyId: 20,
            partyType: 'customer',
        );

        $return->cancel();

        $this->assertEquals('cancelled', $return->getStatus());
    }

    public function test_stock_return_issue_credit_memo(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-CM-001',
            returnType: 'sales_return',
            partyId: 20,
            partyType: 'customer',
        );

        $return->approve(5);
        $return->complete(3);
        $return->issueCreditMemo('CM-2026-0042');

        $this->assertTrue($return->getCreditMemoIssued());
        $this->assertEquals('CM-2026-0042', $return->getCreditMemoReference());
    }

    public function test_stock_return_update_details(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-UPD-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
            notes: 'Original note',
            returnReason: 'Original reason',
        );

        $return->updateDetails('Updated note', ['key' => 'value'], 'Updated reason');

        $this->assertEquals('Updated note', $return->getNotes());
        $this->assertEquals('Updated reason', $return->getReturnReason());
        $this->assertEquals(['key' => 'value'], $return->getMetadata()->toArray());
    }

    // ========================================================================
    // STOCK RETURN — PARTIAL RETURN SCENARIOS
    // ========================================================================

    public function test_stock_return_partial_return_with_original_reference(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-PARTIAL-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
            originalReferenceId: 200,
            originalReferenceType: 'purchase_order',
            returnReason: 'Partial return: 3 of 10 units damaged',
            totalAmount: 150.00,
        );

        $this->assertNotNull($return->getOriginalReferenceId());
        $this->assertEquals(200, $return->getOriginalReferenceId());
        $this->assertEquals('purchase_order', $return->getOriginalReferenceType());
        $this->assertEquals(150.00, $return->getTotalAmount());
    }

    public function test_stock_return_without_original_reference(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-NOREF-001',
            returnType: 'sales_return',
            partyId: 30,
            partyType: 'customer',
            originalReferenceId: null,
            originalReferenceType: null,
            returnReason: 'Customer return without original order reference',
        );

        $this->assertNull($return->getOriginalReferenceId());
        $this->assertNull($return->getOriginalReferenceType());
    }

    public function test_stock_return_with_restocking_fee(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-FEE-001',
            returnType: 'sales_return',
            partyId: 30,
            partyType: 'customer',
            totalAmount: 500.00,
            restockingFee: 50.00,
            restock: true,
            restockLocationId: 5,
        );

        $this->assertEquals(50.00, $return->getRestockingFee());
        $this->assertTrue($return->getRestock());
        $this->assertEquals(5, $return->getRestockLocationId());
    }

    public function test_stock_return_without_restock(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-NORESTOCK-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
            restock: false,
        );

        $this->assertFalse($return->getRestock());
    }

    // ========================================================================
    // STOCK RETURN LINE ENTITY — DEFAULTS
    // ========================================================================

    public function test_stock_return_line_entity_defaults(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 3.0,
        );

        $this->assertEquals(1, $line->getTenantId());
        $this->assertEquals(10, $line->getStockReturnId());
        $this->assertEquals(50, $line->getProductId());
        $this->assertEquals(3.0, $line->getQuantityRequested());
        $this->assertEquals('good', $line->getCondition());
        $this->assertEquals('restock', $line->getDisposition());
        $this->assertEquals('pending', $line->getQualityCheckStatus());
        $this->assertNull($line->getId());
        $this->assertNull($line->getVariationId());
        $this->assertNull($line->getBatchId());
        $this->assertNull($line->getSerialNumberId());
        $this->assertNull($line->getUomId());
        $this->assertNull($line->getQuantityApproved());
        $this->assertNull($line->getUnitPrice());
        $this->assertNull($line->getUnitCost());
        $this->assertNull($line->getNotes());
        $this->assertNull($line->getQualityCheckedBy());
        $this->assertNull($line->getQualityCheckedAt());
    }

    public function test_stock_return_line_with_batch_and_serial(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 1.0,
            variationId: 3,
            batchId: 7,
            serialNumberId: 99,
            uomId: 2,
            unitPrice: 100.00,
            unitCost: 75.00,
        );

        $this->assertEquals(3, $line->getVariationId());
        $this->assertEquals(7, $line->getBatchId());
        $this->assertEquals(99, $line->getSerialNumberId());
        $this->assertEquals(2, $line->getUomId());
        $this->assertEquals(100.00, $line->getUnitPrice());
        $this->assertEquals(75.00, $line->getUnitCost());
    }

    public function test_stock_return_line_without_batch_or_serial(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 5.0,
            batchId: null,
            serialNumberId: null,
        );

        $this->assertNull($line->getBatchId());
        $this->assertNull($line->getSerialNumberId());
        $this->assertEquals(5.0, $line->getQuantityRequested());
    }

    public function test_stock_return_line_damaged_condition(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
            condition: 'damaged',
            disposition: 'scrap',
        );

        $this->assertEquals('damaged', $line->getCondition());
        $this->assertEquals('scrap', $line->getDisposition());
    }

    public function test_stock_return_line_defective_with_vendor_return(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 1.0,
            condition: 'defective',
            disposition: 'vendor_return',
        );

        $this->assertEquals('defective', $line->getCondition());
        $this->assertEquals('vendor_return', $line->getDisposition());
    }

    public function test_stock_return_line_expired_with_quarantine(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 4.0,
            condition: 'expired',
            disposition: 'quarantine',
        );

        $this->assertEquals('expired', $line->getCondition());
        $this->assertEquals('quarantine', $line->getDisposition());
    }

    // ========================================================================
    // STOCK RETURN LINE ENTITY — STATE TRANSITIONS
    // ========================================================================

    public function test_stock_return_line_approve_quantity(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 5.0,
        );

        $line->approve(3.0);

        $this->assertEquals(3.0, $line->getQuantityApproved());
    }

    public function test_stock_return_line_partial_approval(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 10.0,
        );

        $line->approve(7.0);

        $this->assertEquals(7.0, $line->getQuantityApproved());
        $this->assertLessThan($line->getQuantityRequested(), $line->getQuantityApproved());
    }

    public function test_stock_return_line_pass_quality_check(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
        );

        $line->passQualityCheck(15);

        $this->assertEquals('passed', $line->getQualityCheckStatus());
        $this->assertEquals(15, $line->getQualityCheckedBy());
        $this->assertInstanceOf(\DateTimeInterface::class, $line->getQualityCheckedAt());
    }

    public function test_stock_return_line_fail_quality_check(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
        );

        $line->failQualityCheck(15);

        $this->assertEquals('failed', $line->getQualityCheckStatus());
        $this->assertEquals(15, $line->getQualityCheckedBy());
        $this->assertInstanceOf(\DateTimeInterface::class, $line->getQualityCheckedAt());
    }

    public function test_stock_return_line_update_details(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
            condition: 'good',
            disposition: 'restock',
            notes: 'Original note',
        );

        $line->updateDetails('Updated note', 'damaged', 'scrap');

        $this->assertEquals('Updated note', $line->getNotes());
        $this->assertEquals('damaged', $line->getCondition());
        $this->assertEquals('scrap', $line->getDisposition());
    }

    public function test_stock_return_line_update_details_partial(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
            condition: 'good',
            disposition: 'restock',
            notes: 'Original note',
        );

        $line->updateDetails(null, 'damaged', null);

        $this->assertEquals('Original note', $line->getNotes());
        $this->assertEquals('damaged', $line->getCondition());
        $this->assertEquals('restock', $line->getDisposition());
    }

    public function test_stock_return_line_timestamps_set_by_default(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 1.0,
        );

        $this->assertInstanceOf(\DateTimeInterface::class, $line->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $line->getUpdatedAt());
    }

    // ========================================================================
    // DOMAIN EVENTS — STOCK RETURN
    // ========================================================================

    public function test_stock_return_created_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );
        $event = new StockReturnCreated($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
        $this->assertSame($return, $event->stockReturn);
    }

    public function test_stock_return_updated_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-002',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );
        $event = new StockReturnUpdated($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_deleted_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-003',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );
        $event = new StockReturnDeleted($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_approved_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-004',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );
        $event = new StockReturnApproved($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_rejected_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-005',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );
        $event = new StockReturnRejected($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_completed_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-006',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );
        $event = new StockReturnCompleted($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_cancelled_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-007',
            returnType: 'sales_return',
            partyId: 20,
            partyType: 'customer',
        );
        $event = new StockReturnCancelled($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_credit_memo_issued_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-EVT-CM-001',
            returnType: 'sales_return',
            partyId: 20,
            partyType: 'customer',
        );
        $event = new StockReturnCreditMemoIssued($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
        $this->assertSame($return, $event->stockReturn);
    }

    public function test_stock_return_credit_memo_issued_event_broadcast_with(): void
    {
        $return = new StockReturn(
            tenantId: 5,
            referenceNumber: 'RET-EVT-CM-002',
            returnType: 'sales_return',
            partyId: 20,
            partyType: 'customer',
            creditMemoReference: 'CM-2026-0099',
        );
        $event = new StockReturnCreditMemoIssued($return);
        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('tenant_id', $payload);
        $this->assertArrayHasKey('credit_memo_reference', $payload);
        $this->assertEquals(5, $payload['tenant_id']);
        $this->assertEquals('CM-2026-0099', $payload['credit_memo_reference']);
    }

    // ========================================================================
    // DOMAIN EVENTS — STOCK RETURN LINE
    // ========================================================================

    public function test_stock_return_line_created_event_extends_base_event(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
        );
        $event = new StockReturnLineCreated($line);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_line_updated_event_extends_base_event(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
        );
        $event = new StockReturnLineUpdated($line);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_line_deleted_event_extends_base_event(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
        );
        $event = new StockReturnLineDeleted($line);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_line_passed_event_extends_base_event(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
        );
        $event = new StockReturnLinePassed($line);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    public function test_stock_return_line_failed_event_extends_base_event(): void
    {
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 10,
            productId: 50,
            quantityRequested: 2.0,
        );
        $event = new StockReturnLineFailed($line);
        $this->assertInstanceOf(BaseEvent::class, $event);
    }

    // ========================================================================
    // EXCEPTIONS
    // ========================================================================

    public function test_stock_return_not_found_exception(): void
    {
        $e = new StockReturnNotFoundException(42);
        $this->assertStringContainsString('42', $e->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    public function test_stock_return_line_not_found_exception(): void
    {
        $e = new StockReturnLineNotFoundException(99);
        $this->assertStringContainsString('99', $e->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    // ========================================================================
    // DTOs
    // ========================================================================

    public function test_stock_return_data_extends_base_dto(): void
    {
        $this->assertTrue(is_a(StockReturnData::class, BaseDto::class, true));
    }

    public function test_update_stock_return_data_extends_base_dto(): void
    {
        $this->assertTrue(is_a(UpdateStockReturnData::class, BaseDto::class, true));
    }

    public function test_stock_return_line_data_extends_base_dto(): void
    {
        $this->assertTrue(is_a(StockReturnLineData::class, BaseDto::class, true));
    }

    public function test_update_stock_return_line_data_extends_base_dto(): void
    {
        $this->assertTrue(is_a(UpdateStockReturnLineData::class, BaseDto::class, true));
    }

    // ========================================================================
    // SERVICE INTERFACES — STOCK RETURN
    // ========================================================================

    public function test_create_stock_return_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(CreateStockReturnServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_find_stock_return_service_interface_is_read_service(): void
    {
        $this->assertTrue(is_a(FindStockReturnServiceInterface::class, ReadServiceInterface::class, true));
    }

    public function test_update_stock_return_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(UpdateStockReturnServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_delete_stock_return_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(DeleteStockReturnServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_approve_stock_return_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(ApproveStockReturnServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_reject_stock_return_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(RejectStockReturnServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_complete_stock_return_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(CompleteStockReturnServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_cancel_stock_return_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(CancelStockReturnServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_issue_credit_memo_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(IssueCreditMemoServiceInterface::class, WriteServiceInterface::class, true));
    }

    // ========================================================================
    // SERVICE INTERFACES — STOCK RETURN LINE
    // ========================================================================

    public function test_create_stock_return_line_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(CreateStockReturnLineServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_find_stock_return_line_service_interface_is_read_service(): void
    {
        $this->assertTrue(is_a(FindStockReturnLineServiceInterface::class, ReadServiceInterface::class, true));
    }

    public function test_update_stock_return_line_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(UpdateStockReturnLineServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_delete_stock_return_line_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(DeleteStockReturnLineServiceInterface::class, WriteServiceInterface::class, true));
    }

    // ========================================================================
    // SERVICES EXTEND BASE SERVICE
    // ========================================================================

    public function test_create_stock_return_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(CreateStockReturnService::class, BaseService::class, true));
    }

    public function test_find_stock_return_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(FindStockReturnService::class, BaseService::class, true));
    }

    public function test_approve_stock_return_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(ApproveStockReturnService::class, BaseService::class, true));
    }

    public function test_reject_stock_return_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(RejectStockReturnService::class, BaseService::class, true));
    }

    public function test_complete_stock_return_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(CompleteStockReturnService::class, BaseService::class, true));
    }

    public function test_cancel_stock_return_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(CancelStockReturnService::class, BaseService::class, true));
    }

    public function test_issue_credit_memo_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(IssueCreditMemoService::class, BaseService::class, true));
    }

    public function test_create_stock_return_line_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(CreateStockReturnLineService::class, BaseService::class, true));
    }

    // ========================================================================
    // SERVICES IMPLEMENT INTERFACES
    // ========================================================================

    public function test_cancel_stock_return_service_implements_interface(): void
    {
        $this->assertTrue(is_a(CancelStockReturnService::class, CancelStockReturnServiceInterface::class, true));
    }

    public function test_issue_credit_memo_service_implements_interface(): void
    {
        $this->assertTrue(is_a(IssueCreditMemoService::class, IssueCreditMemoServiceInterface::class, true));
    }

    // ========================================================================
    // INFRASTRUCTURE — MODELS
    // ========================================================================

    public function test_stock_return_model_table(): void
    {
        $model = new StockReturnModel;
        $this->assertEquals('stock_returns', $model->getTable());
    }

    public function test_stock_return_line_model_table(): void
    {
        $model = new StockReturnLineModel;
        $this->assertEquals('stock_return_lines', $model->getTable());
    }

    public function test_stock_return_model_fillable_includes_tenant_id(): void
    {
        $model = new StockReturnModel;
        $this->assertContains('tenant_id', $model->getFillable());
    }

    public function test_stock_return_model_fillable_includes_return_type(): void
    {
        $model = new StockReturnModel;
        $this->assertContains('return_type', $model->getFillable());
    }

    public function test_stock_return_model_fillable_includes_status(): void
    {
        $model = new StockReturnModel;
        $this->assertContains('status', $model->getFillable());
    }

    public function test_stock_return_model_fillable_includes_credit_memo_fields(): void
    {
        $model = new StockReturnModel;
        $this->assertContains('credit_memo_issued', $model->getFillable());
        $this->assertContains('credit_memo_reference', $model->getFillable());
    }

    public function test_stock_return_model_fillable_includes_restocking_fields(): void
    {
        $model = new StockReturnModel;
        $this->assertContains('restock', $model->getFillable());
        $this->assertContains('restocking_fee', $model->getFillable());
        $this->assertContains('restock_location_id', $model->getFillable());
    }

    public function test_stock_return_line_model_fillable_includes_batch_and_serial(): void
    {
        $model = new StockReturnLineModel;
        $this->assertContains('batch_id', $model->getFillable());
        $this->assertContains('serial_number_id', $model->getFillable());
    }

    public function test_stock_return_line_model_fillable_includes_quality_check(): void
    {
        $model = new StockReturnLineModel;
        $this->assertContains('quality_check_status', $model->getFillable());
        $this->assertContains('quality_checked_by', $model->getFillable());
        $this->assertContains('quality_checked_at', $model->getFillable());
    }

    public function test_stock_return_line_model_fillable_includes_condition_and_disposition(): void
    {
        $model = new StockReturnLineModel;
        $this->assertContains('condition', $model->getFillable());
        $this->assertContains('disposition', $model->getFillable());
    }

    // ========================================================================
    // INFRASTRUCTURE — REPOSITORIES
    // ========================================================================

    public function test_eloquent_stock_return_repo_implements_interface(): void
    {
        $this->assertTrue(is_a(
            EloquentStockReturnRepository::class,
            StockReturnRepositoryInterface::class,
            true
        ));
    }

    public function test_eloquent_stock_return_line_repo_implements_interface(): void
    {
        $this->assertTrue(is_a(
            EloquentStockReturnLineRepository::class,
            StockReturnLineRepositoryInterface::class,
            true
        ));
    }

    // ========================================================================
    // INFRASTRUCTURE — HTTP LAYER
    // ========================================================================

    public function test_stock_return_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(StockReturnController::class));
    }

    public function test_stock_return_line_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(StockReturnLineController::class));
    }

    public function test_store_stock_return_request_class_exists(): void
    {
        $this->assertTrue(class_exists(StoreStockReturnRequest::class));
    }

    public function test_update_stock_return_request_class_exists(): void
    {
        $this->assertTrue(class_exists(UpdateStockReturnRequest::class));
    }

    public function test_store_stock_return_line_request_class_exists(): void
    {
        $this->assertTrue(class_exists(StoreStockReturnLineRequest::class));
    }

    public function test_update_stock_return_line_request_class_exists(): void
    {
        $this->assertTrue(class_exists(UpdateStockReturnLineRequest::class));
    }

    public function test_stock_return_resource_class_exists(): void
    {
        $this->assertTrue(class_exists(StockReturnResource::class));
    }

    public function test_stock_return_collection_class_exists(): void
    {
        $this->assertTrue(class_exists(StockReturnCollection::class));
    }

    public function test_stock_return_line_resource_class_exists(): void
    {
        $this->assertTrue(class_exists(StockReturnLineResource::class));
    }

    public function test_stock_return_line_collection_class_exists(): void
    {
        $this->assertTrue(class_exists(StockReturnLineCollection::class));
    }

    // ========================================================================
    // SERVICE PROVIDER
    // ========================================================================

    public function test_returns_service_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(ReturnsServiceProvider::class));
    }

    // ========================================================================
    // FULL WORKFLOW — PURCHASE RETURN TO SUPPLIER
    // ========================================================================

    public function test_full_purchase_return_workflow_entity_flow(): void
    {
        // Step 1: Create a purchase return (draft)
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-WF-PO-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
            originalReferenceId: 300,
            originalReferenceType: 'purchase_order',
            returnReason: '3 units arrived damaged',
            totalAmount: 225.00,
            restock: false,
            restockingFee: 0.0,
        );

        $this->assertEquals('draft', $return->getStatus());
        $this->assertTrue($return->isPurchaseReturn());

        // Step 2: Add a return line with damaged condition
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 0,
            productId: 101,
            quantityRequested: 3.0,
            batchId: 42,
            serialNumberId: null,
            unitCost: 75.00,
            condition: 'damaged',
            disposition: 'vendor_return',
        );

        $this->assertEquals('damaged', $line->getCondition());
        $this->assertEquals('vendor_return', $line->getDisposition());
        $this->assertEquals(3.0, $line->getQuantityRequested());
        $this->assertEquals(42, $line->getBatchId());

        // Step 3: Approve partial quantity
        $line->approve(3.0);
        $this->assertEquals(3.0, $line->getQuantityApproved());

        // Step 4: Quality check
        $line->failQualityCheck(20);
        $this->assertEquals('failed', $line->getQualityCheckStatus());

        // Step 5: Approve the return
        $return->approve(10);
        $this->assertEquals('approved', $return->getStatus());

        // Step 6: Complete the return
        $return->complete(7);
        $this->assertEquals('completed', $return->getStatus());
        $this->assertEquals(7, $return->getProcessedBy());
    }

    public function test_full_sales_return_with_credit_memo_workflow(): void
    {
        // Step 1: Create a sales return
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-WF-SO-001',
            returnType: 'sales_return',
            partyId: 30,
            partyType: 'customer',
            originalReferenceId: 500,
            originalReferenceType: 'sales_order',
            returnReason: 'Customer not satisfied with quality',
            totalAmount: 350.00,
            restock: true,
            restockLocationId: 2,
            restockingFee: 35.00,
        );

        $this->assertTrue($return->isSalesReturn());
        $this->assertEquals('draft', $return->getStatus());

        // Step 2: Return line with good condition for restock
        $line = new StockReturnLine(
            tenantId: 1,
            stockReturnId: 0,
            productId: 202,
            quantityRequested: 2.0,
            serialNumberId: 55,
            unitPrice: 175.00,
            condition: 'good',
            disposition: 'restock',
        );

        $this->assertEquals('good', $line->getCondition());
        $this->assertEquals('restock', $line->getDisposition());
        $this->assertEquals(55, $line->getSerialNumberId());

        // Step 3: Quality check passes
        $line->passQualityCheck(18);
        $this->assertEquals('passed', $line->getQualityCheckStatus());

        // Step 4: Approve partial quantity
        $line->approve(2.0);

        // Step 5: Approve the return
        $return->approve(10);

        // Step 6: Complete
        $return->complete(8);

        // Step 7: Issue credit memo
        $return->issueCreditMemo('CM-2026-0101');

        $this->assertTrue($return->getCreditMemoIssued());
        $this->assertEquals('CM-2026-0101', $return->getCreditMemoReference());
        $this->assertEquals('completed', $return->getStatus());
    }

    public function test_purchase_return_cancellation_workflow(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-CAN-WF-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
            returnReason: 'Return initiated but supplier rejected',
        );

        $this->assertEquals('draft', $return->getStatus());

        $return->cancel();

        $this->assertEquals('cancelled', $return->getStatus());
        $this->assertFalse($return->getCreditMemoIssued());
    }

    // ========================================================================
    // MULTI-TENANT ISOLATION
    // ========================================================================

    public function test_stock_return_tenant_isolation(): void
    {
        $return1 = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-T1-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );

        $return2 = new StockReturn(
            tenantId: 2,
            referenceNumber: 'RET-T2-001',
            returnType: 'sales_return',
            partyId: 20,
            partyType: 'customer',
        );

        $this->assertEquals(1, $return1->getTenantId());
        $this->assertEquals(2, $return2->getTenantId());
        $this->assertNotEquals($return1->getTenantId(), $return2->getTenantId());
    }

    // ========================================================================
    // DOMAIN EVENT — StockReturnInventoryAdjusted
    // ========================================================================

    public function test_stock_return_inventory_adjusted_event_extends_base_event(): void
    {
        $return = new StockReturn(
            tenantId: 1,
            referenceNumber: 'RET-ADJ-001',
            returnType: 'purchase_return',
            partyId: 5,
            partyType: 'supplier',
        );
        $event = new StockReturnInventoryAdjusted($return);
        $this->assertInstanceOf(BaseEvent::class, $event);
        $this->assertSame($return, $event->stockReturn);
    }

    public function test_stock_return_inventory_adjusted_event_broadcast_with(): void
    {
        $return = new StockReturn(
            tenantId: 3,
            referenceNumber: 'RET-ADJ-002',
            returnType: 'sales_return',
            partyId: 30,
            partyType: 'customer',
            restockLocationId: 7,
        );
        $event   = new StockReturnInventoryAdjusted($return);
        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('tenant_id', $payload);
        $this->assertArrayHasKey('reference_number', $payload);
        $this->assertArrayHasKey('restock_location_id', $payload);
        $this->assertEquals(3, $payload['tenant_id']);
        $this->assertEquals('RET-ADJ-002', $payload['reference_number']);
        $this->assertEquals(7, $payload['restock_location_id']);
    }

    // ========================================================================
    // PROCESS RETURN INVENTORY ADJUSTMENT — SERVICE INTERFACE & CLASS
    // ========================================================================

    public function test_process_return_inventory_adjustment_service_interface_is_write_service(): void
    {
        $this->assertTrue(is_a(ProcessReturnInventoryAdjustmentServiceInterface::class, WriteServiceInterface::class, true));
    }

    public function test_process_return_inventory_adjustment_service_extends_base_service(): void
    {
        $this->assertTrue(is_a(ProcessReturnInventoryAdjustmentService::class, BaseService::class, true));
    }

    public function test_process_return_inventory_adjustment_service_implements_interface(): void
    {
        $this->assertTrue(is_a(ProcessReturnInventoryAdjustmentService::class, ProcessReturnInventoryAdjustmentServiceInterface::class, true));
    }

    // ========================================================================
    // INVENTORY LAYER — VALUATION METHOD SCENARIOS
    // ========================================================================

    public function test_inventory_valuation_layer_entity_created_for_restock_return(): void
    {
        // When a return line is restocked, a new valuation layer should be
        // created at the return's unit cost. This test verifies the entity
        // correctly represents a FIFO layer created from a sales return.
        $layerDate = new \DateTimeImmutable('2026-04-02');
        $layer     = new \Modules\Inventory\Domain\Entities\InventoryValuationLayer(
            tenantId:        1,
            productId:       101,
            layerDate:       $layerDate,
            qtyIn:           3.0,
            unitCost:        75.00,
            valuationMethod: 'fifo',
            variationId:     null,
            batchId:         42,
            locationId:      5,
            qtyRemaining:    3.0,
            currency:        'USD',
            referenceType:   'stock_return',
            referenceId:     1001,
        );

        $this->assertEquals(1, $layer->getTenantId());
        $this->assertEquals(101, $layer->getProductId());
        $this->assertEquals(3.0, $layer->getQtyIn());
        $this->assertEquals(3.0, $layer->getQtyRemaining());
        $this->assertEquals(75.00, $layer->getUnitCost());
        $this->assertEquals('fifo', $layer->getValuationMethod());
        $this->assertEquals('stock_return', $layer->getReferenceType());
        $this->assertEquals(1001, $layer->getReferenceId());
        $this->assertEquals(225.00, $layer->getTotalValue());
        $this->assertFalse($layer->isClosed());
    }

    public function test_inventory_valuation_layer_fifo_method(): void
    {
        $layer = new \Modules\Inventory\Domain\Entities\InventoryValuationLayer(
            tenantId:        1,
            productId:       200,
            layerDate:       new \DateTimeImmutable,
            qtyIn:           5.0,
            unitCost:        100.00,
            valuationMethod: 'fifo',
        );

        $this->assertEquals('fifo', $layer->getValuationMethod());
        $this->assertEquals(500.00, $layer->getTotalValue());
    }

    public function test_inventory_valuation_layer_lifo_method(): void
    {
        $layer = new \Modules\Inventory\Domain\Entities\InventoryValuationLayer(
            tenantId:        1,
            productId:       200,
            layerDate:       new \DateTimeImmutable,
            qtyIn:           5.0,
            unitCost:        120.00,
            valuationMethod: 'lifo',
        );

        $this->assertEquals('lifo', $layer->getValuationMethod());
        $this->assertEquals(600.00, $layer->getTotalValue());
    }

    public function test_inventory_valuation_layer_avco_method(): void
    {
        $layer = new \Modules\Inventory\Domain\Entities\InventoryValuationLayer(
            tenantId:        1,
            productId:       200,
            layerDate:       new \DateTimeImmutable,
            qtyIn:           10.0,
            unitCost:        90.00,
            valuationMethod: 'avco',
        );

        $this->assertEquals('avco', $layer->getValuationMethod());
        $this->assertEquals(900.00, $layer->getTotalValue());
    }

    public function test_inventory_valuation_layer_consume_reduces_qty_remaining(): void
    {
        $layer = new \Modules\Inventory\Domain\Entities\InventoryValuationLayer(
            tenantId:        1,
            productId:       300,
            layerDate:       new \DateTimeImmutable,
            qtyIn:           10.0,
            unitCost:        50.00,
            valuationMethod: 'fifo',
        );

        $consumed = $layer->consume(4.0);

        $this->assertEquals(4.0, $consumed);
        $this->assertEquals(6.0, $layer->getQtyRemaining());
        $this->assertFalse($layer->isClosed());
    }

    public function test_inventory_valuation_layer_consume_closes_when_fully_consumed(): void
    {
        $layer = new \Modules\Inventory\Domain\Entities\InventoryValuationLayer(
            tenantId:        1,
            productId:       300,
            layerDate:       new \DateTimeImmutable,
            qtyIn:           5.0,
            unitCost:        50.00,
            valuationMethod: 'fifo',
        );

        $layer->consume(5.0);

        $this->assertEquals(0.0, $layer->getQtyRemaining());
        $this->assertTrue($layer->isClosed());
        $this->assertEquals(0.0, $layer->getTotalValue());
    }

    public function test_inventory_level_add_stock_for_restock_return(): void
    {
        // Simulates inventory level being incremented when a return is restocked.
        $level = new \Modules\Inventory\Domain\Entities\InventoryLevel(
            tenantId:    1,
            productId:   101,
            locationId:  5,
            qtyOnHand:   10.0,
            qtyReserved: 2.0,
        );

        $this->assertEquals(10.0, $level->getQtyOnHand());
        $this->assertEquals(8.0, $level->getQtyAvailable());

        $level->addStock(3.0); // 3 units returned and restocked

        $this->assertEquals(13.0, $level->getQtyOnHand());
        $this->assertEquals(11.0, $level->getQtyAvailable());
    }

    // ========================================================================
    // STOCK MOVEMENT — RETURN_IN / RETURN_OUT / ADJUSTMENT MOVEMENT TYPES
    // ========================================================================

    public function test_stock_movement_return_in_type_for_restock(): void
    {
        $movement = new \Modules\StockMovement\Domain\Entities\StockMovement(
            tenantId:        1,
            referenceNumber: 'MOV-RET-PO-001-101',
            movementType:    'return_in',
            productId:       101,
            quantity:        3.0,
            toLocationId:    5,
            batchId:         42,
            unitCost:        75.00,
            currency:        'USD',
            referenceType:   'stock_return',
            referenceId:     1001,
        );

        $movement->confirm();

        $this->assertEquals('return_in', $movement->getMovementType());
        $this->assertEquals('confirmed', $movement->getStatus());
        $this->assertEquals(3.0, $movement->getQuantity());
        $this->assertEquals('stock_return', $movement->getReferenceType());
        $this->assertEquals(1001, $movement->getReferenceId());
        $this->assertTrue($movement->isConfirmed());
    }

    public function test_stock_movement_return_out_type_for_vendor_return(): void
    {
        $movement = new \Modules\StockMovement\Domain\Entities\StockMovement(
            tenantId:        1,
            referenceNumber: 'MOV-RET-VENDOR-001-101',
            movementType:    'return_out',
            productId:       101,
            quantity:        2.0,
            unitCost:        80.00,
            referenceType:   'stock_return',
            referenceId:     2001,
        );

        $movement->confirm();

        $this->assertEquals('return_out', $movement->getMovementType());
        $this->assertEquals('confirmed', $movement->getStatus());
        $this->assertTrue($movement->isConfirmed());
    }

    public function test_stock_movement_adjustment_type_for_scrap(): void
    {
        $movement = new \Modules\StockMovement\Domain\Entities\StockMovement(
            tenantId:        1,
            referenceNumber: 'MOV-RET-SCRAP-001-101',
            movementType:    'adjustment',
            productId:       101,
            quantity:        1.0,
            unitCost:        60.00,
            referenceType:   'stock_return',
            referenceId:     3001,
        );

        $movement->confirm();

        $this->assertEquals('adjustment', $movement->getMovementType());
        $this->assertEquals('confirmed', $movement->getStatus());
    }

    // ========================================================================
    // FULL WORKFLOW — INVENTORY IMPACT VERIFICATION
    // ========================================================================

    public function test_restock_disposition_adds_stock_and_creates_valuation_layer(): void
    {
        // Verifies the entity-level behavior that underpins the
        // ProcessReturnInventoryAdjustmentService for restock lines.
        $level = new \Modules\Inventory\Domain\Entities\InventoryLevel(
            tenantId:   1,
            productId:  101,
            locationId: 5,
            qtyOnHand:  20.0,
        );
        $originalQty = $level->getQtyOnHand();

        // Simulate return of 4 good units
        $returnedQty = 4.0;
        $unitCost    = 50.00;

        $level->addStock($returnedQty);

        $layer = new \Modules\Inventory\Domain\Entities\InventoryValuationLayer(
            tenantId:        1,
            productId:       101,
            layerDate:       new \DateTimeImmutable,
            qtyIn:           $returnedQty,
            unitCost:        $unitCost,
            valuationMethod: 'fifo',
            locationId:      5,
            qtyRemaining:    $returnedQty,
            referenceType:   'stock_return',
            referenceId:     42,
        );

        $this->assertEquals($originalQty + $returnedQty, $level->getQtyOnHand());
        $this->assertEquals($returnedQty, $layer->getQtyIn());
        $this->assertEquals($unitCost * $returnedQty, $layer->getTotalValue());
        $this->assertEquals('stock_return', $layer->getReferenceType());
    }

    public function test_scrap_disposition_does_not_add_stock(): void
    {
        // For scrap disposition, only a StockMovement is created; the
        // InventoryLevel must NOT be incremented.
        $level = new \Modules\Inventory\Domain\Entities\InventoryLevel(
            tenantId:   1,
            productId:  202,
            locationId: 5,
            qtyOnHand:  15.0,
        );

        $qtyBefore = $level->getQtyOnHand();

        // Scrap: no addStock() call – level stays the same
        // Only a StockMovement (adjustment) is created
        $movement = new \Modules\StockMovement\Domain\Entities\StockMovement(
            tenantId:      1,
            referenceNumber: 'MOV-SCRAP-001',
            movementType:  'adjustment',
            productId:     202,
            quantity:      2.0,
            referenceType: 'stock_return',
            referenceId:   999,
        );
        $movement->confirm();

        $this->assertEquals($qtyBefore, $level->getQtyOnHand());
        $this->assertEquals('adjustment', $movement->getMovementType());
    }
}
