# Runtime Sequence Matrix

This document maps the principal cross-module runtime flows in KVAutoERP, showing which domain events are fired and which listeners consume them at each step.

---

## 1. Procure-to-Pay (P2P)

| Step | Actor / Service | Domain Event Fired | Listener(s) |
|------|----------------|--------------------|-------------|
| 1. Create Purchase Order | `Purchase` | — | — |
| 2. Confirm Purchase Order | `Purchase` (`ConfirmPurchaseOrderService`) | `PurchaseOrderConfirmed` | — |
| 3. Receive Goods (GRN posted) | `Purchase` (`PostGrnService`) | `GoodsReceiptPosted` | `Inventory\HandleGoodsReceiptPosted` (stock movements + trace), `Supplier\HandleGoodsReceiptPosted` (last_purchase_price) |
| 4. Approve Purchase Invoice | `Purchase` (`ApprovePurchaseInvoiceService`) | `PurchaseInvoiceApproved` | `Finance\HandlePurchaseInvoiceApproved` (AP transaction + journal) |
| 5. Record Payment | `Purchase` (`RecordPurchasePaymentService`) | `PurchasePaymentRecorded` | `Finance\HandlePurchasePaymentRecorded` (payment allocation) |

---

## 2. Order-to-Cash (O2C)

| Step | Actor / Service | Domain Event Fired | Listener(s) |
|------|----------------|--------------------|-------------|
| 1. Create Sales Order | `Sales` | — | — |
| 2. Confirm Sales Order | `Sales` (`ConfirmSalesOrderService`) | `SalesOrderConfirmed` | — |
| 3. Process Shipment | `Sales` (`ProcessShipmentService`) | `ShipmentProcessed` | `Inventory\HandleShipmentProcessed` (outbound stock movement + trace) |
| 4. Post Sales Invoice | `Sales` (`PostSalesInvoiceService`) | `SalesInvoicePosted` | `Finance\HandleSalesInvoicePosted` (AR transaction + journal) |
| 5. Record Payment | `Sales` (`RecordSalesPaymentService`) | `SalesPaymentRecorded` | `Finance\HandleSalesPaymentRecorded` (payment allocation) |

---

## 3. Inventory Movements and Valuation

| Trigger | Service | Domain Event | Downstream |
|---------|---------|--------------|------------ |
| GRN posted | `Purchase\PostGrnService` | `GoodsReceiptPosted` | `Inventory\HandleGoodsReceiptPosted` creates `StockMovement` + `TraceLog`; FIFO/WAC layer calculates running `unit_cost` |
| Shipment processed | `Sales\ProcessShipmentService` | `ShipmentProcessed` | `Inventory\HandleShipmentProcessed` creates outbound `StockMovement` + `TraceLog`; deducts reserved stock |
| Manual adjustment | `Inventory\RecordStockAdjustmentService` | — | Creates `StockMovement` + `TraceLog` synchronously; posts GL via `Finance\CreateJournalEntryService` |
| Reservation | `Inventory\ReserveStockService` | — | Updates `StockLevel.reserved_qty` within same request |
| Reservation release | `inventory:release-expired-reservations` (scheduled every 15 min) | — | Bulk-expires stale `StockReservation` rows |

UOM conversion factors for all movements are resolved via `Product\UomConversionResolverService`. The resolver's underlying `listForResolution` result is cached per tenant (version-based bust on save/delete of `UomConversion` records).

---

## 4. HR to Finance Posting

| Step | Actor / Service | Domain Event Fired | Listener(s) |
|------|----------------|--------------------|-------------|
| 1. Process Payroll Run | `HR\ProcessPayrollRunService` | — | Generates `Payslip` records with `journal_entry_id = null` |
| 2. Approve Payroll Run | `HR\ApprovePayrollRunService` | `PayrollRunApproved` | `Finance\HandlePayrollRunApproved` |
| 3. Journal Entry Created | `Finance\HandlePayrollRunApproved` | — | DR Payroll Expense / CR Payroll Liability / CR Deductions; writes `journal_entry_id` back to all `hr_payslips` rows for the run |

**Journal structure** (double-entry, all amounts in functional currency):

```
DR  Payroll Expense Account   totalGross
CR  Payroll Liability Account totalNet
CR  Payroll Deductions Account totalDeductions
```

Account IDs are read from `PayrollRun.metadata` keys: `payroll_expense_account_id`, `payroll_liability_account_id`, `payroll_deductions_account_id`. If any account is missing the listener skips posting and emits a `Log::warning`.

A replay guard (`HandlesReplayConflicts` trait) prevents duplicate journal entries if the event is delivered more than once.
