# Runtime Sequence Matrix

This document describes the key cross-module runtime flows in KVAutoERP, showing how domain events
propagate through the system and which listeners/services handle each step.

---

## 1. Procure-to-Pay (P2P)

```
Buyer                    Purchase Module              Finance Module              Inventory Module
  |                            |                           |                            |
  |-- POST /purchase-orders -> |                           |                           |
  |                     PurchaseOrderCreated               |                            |
  |-- POST .../confirm  ->     |                           |                            |
  |                     PurchaseOrderConfirmed             |                            |
  |-- POST /grns        ->     |                           |                            |
  |-- POST /grns/{id}/post ->  |                           |                            |
  |                     GoodsReceiptPosted  ------------> HandleGoodsReceiptPosted      |
  |                            |              (Inventory)  | adjustStockLevel()         |
  |-- POST /purchase-invoices  |                           |                            |
  |-- POST .../approve  ->     |                           |                            |
  |                     PurchaseInvoiceApproved ----------> HandlePurchaseInvoiceApproved
  |                            |              (Finance)    | createJournalEntry()       |
  |                            |                           | createApTransaction()      |
  |-- POST .../payment  ->     |                           |                            |
  |                     PurchasePaymentRecorded ----------> HandlePurchasePaymentRecorded
  |                            |              (Finance)    | createJournalEntry()       |
  |                            |                           | reconcileApTransaction()   |
```

**Services involved**: `ConfirmPurchaseOrderService`, `PostGrnService`, `ApprovePurchaseInvoiceService`, `RecordPurchasePaymentService`

**Events fired**: `PurchaseOrderConfirmed`, `GoodsReceiptPosted`, `PurchaseInvoiceApproved`, `PurchasePaymentRecorded`

---

## 2. Order-to-Cash (O2C)

```
Customer                Sales Module                Finance Module              Inventory Module
  |                         |                           |                            |
  |-- POST /sales-orders -> |                           |                            |
  |-- POST .../confirm ->   |                           |                            |
  |                   SalesOrderConfirmed               |                            |
  |-- POST /shipments  ->   |                           |                            |
  |-- POST .../process ->   |                           |                            |
  |                   ShipmentProcessed  -----------------------------------------> HandleShipmentProcessed
  |                         |              (Inventory)                               | adjustStockLevel()
  |-- POST /sales-invoices  |                           |                            |
  |-- POST .../post    ->   |                           |                            |
  |                   SalesInvoicePosted ------------> HandleSalesInvoicePosted      |
  |                         |              (Finance)    | createJournalEntry()       |
  |                         |                           | createArTransaction()      |
  |-- POST .../record-payment ->  |                     |                            |
  |                   SalesPaymentRecorded -----------> HandleSalesPaymentRecorded   |
  |                         |              (Finance)    | createJournalEntry()       |
  |                         |                           | reconcileArTransaction()   |
```

**Services involved**: `ConfirmSalesOrderService`, `ProcessShipmentService`, `PostSalesInvoiceService`, `RecordSalesPaymentService`

**Events fired**: `SalesOrderConfirmed`, `ShipmentProcessed`, `SalesInvoicePosted`, `SalesPaymentRecorded`

---

## 3. Inventory Movements and Valuation

Inventory stock levels are adjusted in response to domain events from other modules. No direct
service-to-service calls cross module boundaries — all integration is event-driven.

| Event                   | Source Module | Handler (Inventory)              | Action                              |
|-------------------------|---------------|----------------------------------|-------------------------------------|
| `GoodsReceiptPosted`    | Purchase      | `HandleGoodsReceiptPosted`       | `StockMovement(receipt)` + increment |
| `ShipmentProcessed`     | Sales         | `HandleShipmentProcessed`        | `StockMovement(shipment)` + decrement |
| `PurchaseReturnPosted`  | Purchase      | `HandlePurchaseReturnPosted`     | `StockMovement(return_to_supplier)` + decrement |
| `SalesReturnReceived`   | Sales         | `HandleSalesReturnReceived`      | `StockMovement(customer_return)` + increment |
| `StockAdjustmentRecorded` | Inventory   | Finance: `HandleStockAdjustmentRecorded` | Journal entry for inventory adjustment |
| `CycleCountCompleted`   | Inventory     | Finance: `HandleCycleCountCompleted` | Journal entry for count variance |

All Finance listeners use the `HandlesReplayConflicts` trait for idempotency and implement
`ShouldQueue` with `afterCommit = true` to ensure events are processed after the originating
transaction commits. Retries: 3 attempts with 30 s backoff.

---

## 4. HR to Finance Posting

Payroll costs are posted to the General Ledger when a payroll run is approved.

```
HR Admin              HR Module                    Finance Module
  |                       |                              |
  |-- Approve PayrollRun  |                              |
  |                 PayrollRunApproved ----------------> HandlePayrollRunApproved
  |                       |               (Finance)      | createJournalEntry()
  |                       |                              |  DR: Payroll Expense
  |                       |                              |  CR: Payroll Liability (net wages)
  |                       |                              |  CR: Payroll Deductions (taxes)
```

**Double-entry structure**:
- **DR** Payroll Expense Account → `totalGross`
- **CR** Payroll Liability Account → `totalNet` (net wages payable)
- **CR** Payroll Deductions Account → `totalDeductions` (taxes / other withholdings)

**Known limitation**: The HR `ProcessPayrollRunService` currently sets `journal_entry_id = null` on
payslips. A future enhancement should write the created `JournalEntry` ID back to the payslip for
full traceability.

---

## Queue Configuration

All Finance cross-module listeners are dispatched to the **`finance`** queue. Run the worker with:

```bash
php artisan queue:work --queue=finance
```

For production, configure a supervisor process and set `QUEUE_CONNECTION=redis` (or your preferred
driver) in `.env`.
