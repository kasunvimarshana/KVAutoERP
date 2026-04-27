# Runtime Sequence Matrix (Cross-Module)

This document captures the canonical runtime execution paths for critical, real-time ERP flows and maps each step to concrete code artifacts, failure points, and guardrails.

## Scope
- Procure-to-Pay (P2P)
- Order-to-Cash (O2C)
- Inventory Movements and Valuation
- HR to Finance Posting

## 1. Procure-to-Pay (P2P)

### Sequence
1. Purchase order is created/confirmed.
- Primary entry: `purchase-orders` and `purchase-orders/{purchaseOrder}/confirm`
- Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
- Domain event: [app/Modules/Purchase/Domain/Events/PurchaseOrderConfirmed.php](app/Modules/Purchase/Domain/Events/PurchaseOrderConfirmed.php)

2. Goods receipt (GRN) is posted.
- Primary entry: `grns/{grn}/post`
- Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
- Domain event: [app/Modules/Purchase/Domain/Events/GoodsReceiptPosted.php](app/Modules/Purchase/Domain/Events/GoodsReceiptPosted.php)

3. Inventory consumes goods-receipt event and updates stock state.
- Listener source: [app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php](app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php)
- Stock movement/valuation repositories: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php), [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php)

4. Purchase invoice is approved.
- Primary entry: `purchase-invoices/{invoice}/approve`
- Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
- Domain event: [app/Modules/Purchase/Domain/Events/PurchaseInvoiceApproved.php](app/Modules/Purchase/Domain/Events/PurchaseInvoiceApproved.php)

5. Finance consumes invoice-approved event and posts AP/journal impact.
- Listener source: [app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php](app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php)
- Journal services/repositories: [app/Modules/Finance/Application/Services/CompleteBankReconciliationService.php](app/Modules/Finance/Application/Services/CompleteBankReconciliationService.php), [app/Modules/Finance/Infrastructure/Persistence/Eloquent/Repositories/EloquentApTransactionRepository.php](app/Modules/Finance/Infrastructure/Persistence/Eloquent/Repositories/EloquentApTransactionRepository.php)

6. Purchase payment is recorded.
- Primary entry: `purchase-invoices/{invoice}/payment`
- Source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
- Domain event: [app/Modules/Purchase/Domain/Events/PurchasePaymentRecorded.php](app/Modules/Purchase/Domain/Events/PurchasePaymentRecorded.php)

7. Finance consumes payment event and updates payment/AP state.
- Listener source: [app/Modules/Finance/Infrastructure/Listeners/HandlePurchasePaymentRecorded.php](app/Modules/Finance/Infrastructure/Listeners/HandlePurchasePaymentRecorded.php)
- Payment aggregate references: [app/Modules/Finance/Domain/Entities/Payment.php](app/Modules/Finance/Domain/Entities/Payment.php), [app/Modules/Finance/database/migrations/2024_01_01_120005b_create_payments_table.php](app/Modules/Finance/database/migrations/2024_01_01_120005b_create_payments_table.php)

### High-Risk Failure Points
- Out-of-order event handling between `PurchaseInvoiceApproved` and `PurchasePaymentRecorded` can produce temporary AP inconsistencies.
- Missing/incorrect tenant propagation can break cross-module posting integrity.
- Partial failure between inventory receipt and finance posting may leave materialized state split.

### Existing Guardrails and Tests
- Route/flow baseline: [tests/Feature/PurchaseRoutesTest.php](tests/Feature/PurchaseRoutesTest.php)
- Repository consistency: [tests/Feature/PurchaseOrderRepositoryIntegrationTest.php](tests/Feature/PurchaseOrderRepositoryIntegrationTest.php)
- Finance listener integration: [tests/Feature/FinanceListenerIntegrationTest.php](tests/Feature/FinanceListenerIntegrationTest.php)

## 2. Order-to-Cash (O2C)

### Sequence
1. Sales order is created/confirmed.
- Primary entry: `sales-orders` and `sales-orders/{salesOrder}/confirm`
- Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)

2. Shipment is processed.
- Primary entry: `shipments/{shipment}/process`
- Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
- Domain event: [app/Modules/Sales/Domain/Events/ShipmentProcessed.php](app/Modules/Sales/Domain/Events/ShipmentProcessed.php)

3. Inventory consumes shipment event and decrements stock.
- Listener source: [app/Modules/Inventory/Infrastructure/Listeners/HandleShipmentProcessed.php](app/Modules/Inventory/Infrastructure/Listeners/HandleShipmentProcessed.php)
- Stock movement repository: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php)

4. Sales invoice is posted.
- Primary entry: `sales-invoices/{salesInvoice}/post`
- Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
- Domain event: [app/Modules/Sales/Domain/Events/SalesInvoicePosted.php](app/Modules/Sales/Domain/Events/SalesInvoicePosted.php)

5. Finance consumes invoice-posted event and creates AR/journal impact.
- Listener source: [app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php](app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php)
- AR references: [app/Modules/Finance/Domain/Entities/ArTransaction.php](app/Modules/Finance/Domain/Entities/ArTransaction.php)

6. Payment is recorded against invoice.
- Primary entry: `sales-invoices/{salesInvoice}/record-payment`
- Source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
- Domain event: [app/Modules/Sales/Domain/Events/SalesPaymentRecorded.php](app/Modules/Sales/Domain/Events/SalesPaymentRecorded.php)

### High-Risk Failure Points
- Shipment processed before reservation/availability checks settle can create negative stock edge cases.
- AR posting and payment recording races can produce transient mismatch in outstanding balances.
- Sales return processing without strict sequence control can desynchronize inventory and finance reversal entries.

### Existing Guardrails and Tests
- Route coverage: [tests/Feature/SalesRoutesTest.php](tests/Feature/SalesRoutesTest.php)
- Repository integration baseline: [tests/Feature/SalesOrderRepositoryIntegrationTest.php](tests/Feature/SalesOrderRepositoryIntegrationTest.php)
- Inventory stock movement integrity: [tests/Feature/InventoryStockMovementIntegrationTest.php](tests/Feature/InventoryStockMovementIntegrationTest.php)
- Finance listener integration: [tests/Feature/FinanceListenerIntegrationTest.php](tests/Feature/FinanceListenerIntegrationTest.php)

## 3. Inventory Movements and Valuation

### Sequence
1. Inventory movement is initiated from operational flows (purchase receipt, shipment, transfer, adjustment, reservation).
- Entry surfaces: [app/Modules/Inventory/routes/api.php](app/Modules/Inventory/routes/api.php)
- Key services: [app/Modules/Inventory/Application/Services/CreateStockReservationService.php](app/Modules/Inventory/Application/Services/CreateStockReservationService.php), [app/Modules/Inventory/Application/Services/ApproveTransferOrderService.php](app/Modules/Inventory/Application/Services/ApproveTransferOrderService.php)

2. Stock level updates are persisted with lock/idempotency protections.
- Repository: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php)

3. Cost layers are updated for valuation traceability.
- Repository: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php)
- Entities: [app/Modules/Inventory/Domain/Entities/InventoryCostLayer.php](app/Modules/Inventory/Domain/Entities/InventoryCostLayer.php), [app/Modules/Inventory/Domain/Entities/ValuationConfig.php](app/Modules/Inventory/Domain/Entities/ValuationConfig.php)

4. Trace logs and related metadata are persisted for auditability.
- Repository: [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentTraceLogRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentTraceLogRepository.php)

### High-Risk Failure Points
- Concurrency races in reservation/release/transfer can cause duplicate or missed stock deltas.
- Multi-hop valuation and conversion precision can drift if rounding policy is inconsistent.
- JSON metadata shape drift can reduce forensic value of trace logs.

### Existing Guardrails and Tests
- Allocation strategy: [tests/Feature/InventoryAllocationStrategyIntegrationTest.php](tests/Feature/InventoryAllocationStrategyIntegrationTest.php)
- Stock reservation: [tests/Feature/InventoryStockReservationIntegrationTest.php](tests/Feature/InventoryStockReservationIntegrationTest.php)
- Stock movement: [tests/Feature/InventoryStockMovementIntegrationTest.php](tests/Feature/InventoryStockMovementIntegrationTest.php)
- Valuation strategy: [tests/Feature/InventoryValuationStrategyIntegrationTest.php](tests/Feature/InventoryValuationStrategyIntegrationTest.php)

## 4. HR to Finance Posting

### Sequence
1. Payroll run is processed and approved.
- Primary entry: `payroll-runs/{payroll_run}/process`, `payroll-runs/{payroll_run}/approve`
- Source: [app/Modules/HR/routes/api.php](app/Modules/HR/routes/api.php)
- Domain event: [app/Modules/HR/Domain/Events/PayrollRunApproved.php](app/Modules/HR/Domain/Events/PayrollRunApproved.php)

2. Payslips are generated and linked to payroll run.
- Primary entry: `payslips`, `payslips/{payslip}`
- Source: [app/Modules/HR/routes/api.php](app/Modules/HR/routes/api.php)
- Domain event: [app/Modules/HR/Domain/Events/PayslipGenerated.php](app/Modules/HR/Domain/Events/PayslipGenerated.php)

3. Finance linkage is established through journal entry references.
- FK integrity in migration: [app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php](app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php)
- Related finance aggregate: [app/Modules/Finance/Domain/Entities/JournalEntry.php](app/Modules/Finance/Domain/Entities/JournalEntry.php)

4. Operational reporting uses status/time indexed paths.
- HR run index: [app/Modules/HR/database/migrations/2024_01_01_900010_create_hr_payroll_runs_table.php](app/Modules/HR/database/migrations/2024_01_01_900010_create_hr_payroll_runs_table.php)
- HR payslip index: [app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php](app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php)

### High-Risk Failure Points
- Payroll approval without deterministic status normalization can allow invalid transitions.
- Missing/late journal entry linkage can break accrual visibility and reconciliation.
- Cross-module retries (HR to Finance) can duplicate posting effects if idempotency keys are absent.

### Existing Guardrails and Tests
- HR route and action coverage: [tests/Feature/HRRoutesTest.php](tests/Feature/HRRoutesTest.php), [tests/Feature/HREndpointsAuthenticatedTest.php](tests/Feature/HREndpointsAuthenticatedTest.php)
- Finance route/listener checks: [tests/Feature/FinanceRoutesTest.php](tests/Feature/FinanceRoutesTest.php), [tests/Feature/FinanceListenerIntegrationTest.php](tests/Feature/FinanceListenerIntegrationTest.php)

## 5. Shared Operational Risks Across Flows
- Event ordering assumptions: workflows still depend on producer/consumer sequencing discipline.
- Tenant isolation safety: every cross-module persistence step must preserve `tenant_id` integrity.
- Status-model consistency: mixed status strategies increase transition-validation complexity.
- Replay/idempotency resilience: long-running, retried operations require explicit dedupe controls.

## 6. Suggested Next Guardrail Tests
1. Add explicit sequence assertions for P2P and O2C event order invariants.
2. Add idempotency regression tests around payment and inventory adjustment replay.
3. Add HR-to-Finance contract test validating single journal linkage per approved payroll outcome.
4. Add cross-module tenant-consistency assertion helpers for listener-driven persistence paths.
