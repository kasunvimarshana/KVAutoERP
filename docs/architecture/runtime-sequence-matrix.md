# Runtime Sequence Matrix (Cross-Module)

This document captures canonical runtime execution paths for critical ERP flows and maps each path to concrete artifacts, failure points, and guardrails.

## Scope

- Procure-to-Pay (P2P)
- Order-to-Cash (O2C)
- Inventory Movements and Valuation
- HR to Finance Posting

## 1. Procure-to-Pay (P2P)

### Sequence (P2P)

- Purchase order is created or confirmed.
  Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
  Event: [app/Modules/Purchase/Domain/Events/PurchaseOrderConfirmed.php](app/Modules/Purchase/Domain/Events/PurchaseOrderConfirmed.php)
- Goods receipt (GRN) is posted.
  Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
  Event: [app/Modules/Purchase/Domain/Events/GoodsReceiptPosted.php](app/Modules/Purchase/Domain/Events/GoodsReceiptPosted.php)
- Inventory consumes goods-receipt and updates stock state.
  Listener: [app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php](app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php)
  Repositories: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php), [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php)
- Purchase invoice is approved.
  Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
  Event: [app/Modules/Purchase/Domain/Events/PurchaseInvoiceApproved.php](app/Modules/Purchase/Domain/Events/PurchaseInvoiceApproved.php)
- Finance consumes invoice-approved event and posts AP/journal impact.
  Listener: [app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php](app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php)
- Purchase payment is recorded.
  Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
  Event: [app/Modules/Purchase/Domain/Events/PurchasePaymentRecorded.php](app/Modules/Purchase/Domain/Events/PurchasePaymentRecorded.php)
- Finance consumes payment event and updates payment/AP state.
  Listener: [app/Modules/Finance/Infrastructure/Listeners/HandlePurchasePaymentRecorded.php](app/Modules/Finance/Infrastructure/Listeners/HandlePurchasePaymentRecorded.php)
  Payment refs: [app/Modules/Finance/Domain/Entities/Payment.php](app/Modules/Finance/Domain/Entities/Payment.php), [app/Modules/Finance/database/migrations/2024_01_01_120005b_create_payments_table.php](app/Modules/Finance/database/migrations/2024_01_01_120005b_create_payments_table.php)

### High-Risk Failure Points (P2P)

- Out-of-order handling between invoice-approved and payment-recorded events can create temporary AP inconsistencies.
- Missing tenant propagation can break cross-module posting integrity.
- Partial failure between inventory receipt and finance posting can split materialized state.

### Existing Guardrails and Tests (P2P)

- [tests/Feature/PurchaseRoutesTest.php](tests/Feature/PurchaseRoutesTest.php)
- [tests/Feature/PurchaseOrderRepositoryIntegrationTest.php](tests/Feature/PurchaseOrderRepositoryIntegrationTest.php)
- [tests/Feature/FinanceListenerIntegrationTest.php](tests/Feature/FinanceListenerIntegrationTest.php)

## 2. Order-to-Cash (O2C)

### Sequence (O2C)

- Sales order is created or confirmed.
  Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
- Shipment is processed.
  Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
  Event: [app/Modules/Sales/Domain/Events/ShipmentProcessed.php](app/Modules/Sales/Domain/Events/ShipmentProcessed.php)
- Inventory consumes shipment event and decrements stock.
  Listener: [app/Modules/Inventory/Infrastructure/Listeners/HandleShipmentProcessed.php](app/Modules/Inventory/Infrastructure/Listeners/HandleShipmentProcessed.php)
- Sales invoice is posted.
  Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
  Event: [app/Modules/Sales/Domain/Events/SalesInvoicePosted.php](app/Modules/Sales/Domain/Events/SalesInvoicePosted.php)
- Finance consumes invoice-posted event and creates AR/journal impact.
  Listener: [app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php](app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php)
  AR ref: [app/Modules/Finance/Domain/Entities/ArTransaction.php](app/Modules/Finance/Domain/Entities/ArTransaction.php)
- Payment is recorded against invoice.
  Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
  Event: [app/Modules/Sales/Domain/Events/SalesPaymentRecorded.php](app/Modules/Sales/Domain/Events/SalesPaymentRecorded.php)

### High-Risk Failure Points (O2C)

- Shipment before availability checks settle can create negative stock edge cases.
- AR posting and payment recording races can produce transient outstanding balance mismatch.
- Return sequencing errors can desynchronize inventory and finance reversals.

### Existing Guardrails and Tests (O2C)

- [tests/Feature/SalesRoutesTest.php](tests/Feature/SalesRoutesTest.php)
- [tests/Feature/SalesOrderRepositoryIntegrationTest.php](tests/Feature/SalesOrderRepositoryIntegrationTest.php)
- [tests/Feature/InventoryStockMovementIntegrationTest.php](tests/Feature/InventoryStockMovementIntegrationTest.php)
- [tests/Feature/FinanceListenerIntegrationTest.php](tests/Feature/FinanceListenerIntegrationTest.php)

## 3. Inventory Movements and Valuation

### Sequence (Inventory)

- Movement is initiated from receipt, shipment, transfer, adjustment, and reservation flows.
  Route surface: [app/Modules/Inventory/routes/api.php](app/Modules/Inventory/routes/api.php)
  Service refs: [app/Modules/Inventory/Application/Services/CreateStockReservationService.php](app/Modules/Inventory/Application/Services/CreateStockReservationService.php), [app/Modules/Inventory/Application/Services/ApproveTransferOrderService.php](app/Modules/Inventory/Application/Services/ApproveTransferOrderService.php)
- Stock levels are persisted with lock/idempotency protections.
  Repository: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php)
- Cost layers are updated for valuation traceability.
  Repository: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php)
- Trace logs and metadata are persisted for auditability.
  Repository: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentTraceLogRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentTraceLogRepository.php)

### High-Risk Failure Points (Inventory)

- Reservation/release/transfer concurrency races can cause duplicate or missed deltas.
- Valuation conversion precision can drift under inconsistent rounding policy.
- Metadata shape drift can reduce trace-log forensic value.

### Existing Guardrails and Tests (Inventory)

- [tests/Feature/InventoryAllocationStrategyIntegrationTest.php](tests/Feature/InventoryAllocationStrategyIntegrationTest.php)
- [tests/Feature/InventoryStockReservationIntegrationTest.php](tests/Feature/InventoryStockReservationIntegrationTest.php)
- [tests/Feature/InventoryStockMovementIntegrationTest.php](tests/Feature/InventoryStockMovementIntegrationTest.php)
- [tests/Feature/InventoryValuationStrategyIntegrationTest.php](tests/Feature/InventoryValuationStrategyIntegrationTest.php)

## 4. HR to Finance Posting

### Sequence (HR-Finance)

- Payroll run is processed and approved.
  Source: [app/Modules/HR/routes/api.php](app/Modules/HR/routes/api.php)
  Event: [app/Modules/HR/Domain/Events/PayrollRunApproved.php](app/Modules/HR/Domain/Events/PayrollRunApproved.php)
- Payslips are generated and linked to payroll run.
  Source: [app/Modules/HR/routes/api.php](app/Modules/HR/routes/api.php)
  Event: [app/Modules/HR/Domain/Events/PayslipGenerated.php](app/Modules/HR/Domain/Events/PayslipGenerated.php)
- Finance linkage is established through journal references.
  HR FK: [app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php](app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php)
  Finance ref: [app/Modules/Finance/Domain/Entities/JournalEntry.php](app/Modules/Finance/Domain/Entities/JournalEntry.php)
- Operational reporting uses status and date indexed paths.
  Index refs: [app/Modules/HR/database/migrations/2024_01_01_900010_create_hr_payroll_runs_table.php](app/Modules/HR/database/migrations/2024_01_01_900010_create_hr_payroll_runs_table.php), [app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php](app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php)

### High-Risk Failure Points (HR-Finance)

- Approval without strict status normalization can allow invalid transitions.
- Missing or late journal linkage can break accrual visibility and reconciliation.
- Cross-module retries can duplicate posting effects without idempotency controls.

### Existing Guardrails and Tests (HR-Finance)

- [tests/Feature/HRRoutesTest.php](tests/Feature/HRRoutesTest.php)
- [tests/Feature/HREndpointsAuthenticatedTest.php](tests/Feature/HREndpointsAuthenticatedTest.php)
- [tests/Feature/FinanceRoutesTest.php](tests/Feature/FinanceRoutesTest.php)
- [tests/Feature/FinanceListenerIntegrationTest.php](tests/Feature/FinanceListenerIntegrationTest.php)

## 5. Shared Operational Risks Across Flows

- Event-order assumptions across producer/consumer boundaries.
- Tenant isolation drift in cross-module persistence.
- Mixed status-model strategies increasing transition complexity.
- Replay handling gaps for long-running retried operations.

## 6. Suggested Next Guardrail Tests

1. Add explicit sequence assertions for P2P and O2C event-order invariants.
1. Add idempotency regression tests around payment and inventory adjustment replay.
1. Add HR-to-Finance contract tests validating single journal linkage per approved payroll outcome.
1. Add cross-module tenant-consistency assertion helpers for listener-driven persistence.
