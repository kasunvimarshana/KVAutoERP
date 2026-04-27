# KVAutoERP Enterprise Architecture Audit (2026-04-27)

## 1. Scope and Method

This audit covered:

- Entire repository structure and provider registration.
- All modules under `app/Modules`.
- All module migrations under `app/Modules/**/database/migrations`.
- Core architecture guardrail tests and route guardrail tests.

Validation approach:

- Static architecture and migration review.
- Dependency and migration-footprint inventory.
- Targeted PHPUnit validation after migration refactors.

## 2. Module Inventory and Maturity

| Module | Domain | Application | Infrastructure | Migrations |
| --- | --- | --- | --- | --- |
| Audit | Yes | Yes | Yes | 1 |
| Auth | Yes | Yes | Yes | 0 |
| Configuration | Yes | Yes | Yes | 4 |
| Core | Yes | Yes | Yes | 0 |
| Customer | Yes | Yes | Yes | 3 |
| Employee | Yes | Yes | Yes | 1 |
| Finance | Yes | Yes | Yes | 20 |
| HR | Yes | Yes | Yes | 16 |
| Inventory | Yes | Yes | Yes | 16 |
| OrganizationUnit | Yes | Yes | Yes | 4 |
| Pricing | Yes | Yes | Yes | 4 |
| Product | Yes | Yes | Yes | 13 |
| Purchase | Yes | Yes | Yes | 8 |
| Sales | Yes | Yes | Yes | 8 |
| Shared | No | No | Yes | 0 |
| Supplier | Yes | Yes | Yes | 4 |
| Tax | Yes | Yes | Yes | 4 |
| Tenant | Yes | Yes | Yes | 5 |
| User | Yes | Yes | Yes | 8 |
| Warehouse | Yes | Yes | Yes | 2 |

Total module migrations reviewed: 121.

## 3. Architecture Pattern Assessment

### 3.1 Positive Findings

- Clean Architecture layering is consistently present in implemented modules.
- Provider registration is explicit and centralized in `bootstrap/providers.php`.
- Repository pattern is structurally present (Domain interfaces + Infrastructure implementations).
- Tenant isolation is modeled explicitly via `tenant_id` and route middleware (`resolve.tenant`).
- Guardrail tests enforce key boundaries (Domain/Application not importing Infrastructure).

### 3.2 Boundary and Dependency Model

Primary dependency direction observed:

- `Tenant` and `Configuration` provide foundational references used broadly by transactional modules.
- `OrganizationUnit`, `User`, and `Warehouse` act as shared operational references.
- `Product`, `Pricing`, `Tax` provide catalog/commercial references.
- `Purchase`, `Sales`, `Inventory`, `Finance`, `HR` are transactional modules with heavy relational integration.

## 4. Data Model and Interdependency Map

### 4.1 Core Cross-Module Anchors

- `tenant_id` is the principal partition key across tenant-owned tables.
- Global reference entities are centralized in Configuration (`countries`, `currencies`, `languages`, `timezones`).
- Financial linkage points (`accounts`, `journal_entries`) are referenced from Sales/Purchase/OrganizationUnit/HR.
- Inventory linkage points (`batches`, `serials`, `warehouse_locations`) are referenced from Sales/Purchase/Product.

### 4.2 High-Value Transactional Flows (Real-Time Scenarios)

1. Procure-to-Pay (P2P)

- `purchase_orders` -> `grn_headers`/`grn_lines` -> `purchase_invoices` -> `payments`/`ap_transactions` -> `journal_entries`.

1. Order-to-Cash (O2C)

- `sales_orders` -> `shipments`/`shipment_lines` -> `sales_invoices` -> `payments`/`ar_transactions` -> `journal_entries`.

1. Inventory Movements and Valuation

- `stock_movements`, `stock_levels`, `inventory_cost_layers`, reservations, and transfer orders.
- Product identifiers, serials, and batches provide traceability dimensions.

1. HR to Finance

- Payroll runs and payslips can post to finance (`journal_entries`) for accrual and settlement scenarios.

## 5. Defects and Refactors Applied

The following migration issues were fixed directly (initial development phase policy: modify existing migrations):

1. Removed duplicate foreign key definitions on already-constrained columns:

- `app/Modules/OrganizationUnit/database/migrations/2024_01_01_200003_create_org_unit_attachments_table.php`
- `app/Modules/Purchase/database/migrations/2024_01_01_100001_create_purchase_orders_table.php`
- `app/Modules/Purchase/database/migrations/2024_01_01_100002_create_purchase_order_lines_table.php`

1. Fixed migration formatting inconsistency affecting readability/maintainability:

- `app/Modules/OrganizationUnit/database/migrations/2024_01_01_200003_create_org_unit_attachments_table.php`

1. Added operational indexes for high-frequency status/date query paths:

- `app/Modules/Finance/database/migrations/2024_01_01_120005b_create_payments_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900006_create_hr_leave_requests_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900010_create_hr_payroll_runs_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900015_create_hr_performance_reviews_table.php`

1. Added missing referential constraints for integrity and consistency:

- `hr_leave_requests.approver_id -> users.id`
- `hr_payroll_runs.approved_by -> users.id`
- `hr_payslips.journal_entry_id -> journal_entries.id`
- `hr_performance_reviews.reviewer_id -> users.id`

1. Added payment replay protection and HR-to-Finance listener closure:

- Added `payments.idempotency_key` and unique key (`tenant_id`, `idempotency_key`) to prevent duplicate payment persistence on replay.
- Added Finance listener for `PayrollRunApproved` (`HandlePayrollRunApproved`) and provider wiring to close previously identified HR-to-Finance posting gap.

1. Closed payment repository replay lookup defect revealed by DB-backed validation:

- `EloquentPaymentRepository` custom tenant/idempotency lookups now use scope-free queries so inherited soft-delete global scopes do not reference a non-existent `payments.deleted_at` column.
- Added integration coverage proving same-tenant replay returns the original persisted payment, cross-tenant reuse remains isolated, and no-key requests still create distinct rows.

1. Closed payment insert-race replay gap in the application service:

- `CreatePaymentService` now catches tenant-scoped payment idempotency unique-key collisions and re-reads the winning row instead of surfacing a duplicate-key database error during retried or concurrent creates.
- Added focused unit coverage for the save-then-reread recovery path so sequential replay behavior and duplicate-insert race behavior are both locked in.

1. Added executable HR-to-Finance payroll posting coverage:

- `FinanceListenerIntegrationTest` now exercises `HandlePayrollRunApproved` end-to-end against the real Finance journal persistence path.
- Coverage includes successful payroll expense/liability/deductions posting plus the main skip conditions for missing account metadata, imbalanced totals, and closed fiscal periods.

1. Closed upstream payroll approval workflow gap and payroll-run decimal mapping defect:

- `FinanceListenerIntegrationTest` now also proves `ApprovePayrollRunService` dispatches `PayrollRunApproved` and materializes the downstream Finance journal entry through the real event pipeline.
- `EloquentPayrollRunRepository` now normalizes payroll total values to strings before constructing the domain entity, preventing DB-driver-specific numeric scalar returns from violating the `PayrollRun` domain constructor contract.

1. Extended upstream workflow-to-finance coverage for Purchase and Sales services:

- `FinanceListenerIntegrationTest` now proves `ApprovePurchaseInvoiceService` dispatches `PurchaseInvoiceApproved` and materializes Finance AP journal posting via real listener wiring.
- The same suite now proves `PostSalesInvoiceService` dispatches `SalesInvoicePosted` and materializes Finance AR journal posting via real listener wiring.

1. Closed purchase-payment workflow dispatch defect and added end-to-end coverage:

- `FinanceListenerIntegrationTest` now proves `RecordPurchasePaymentService` dispatches `PurchasePaymentRecorded` and materializes downstream Finance payment/AP posting artifacts.
- `RecordPurchasePaymentService` now uses Finance-valid payment direction `outbound` (previously `outgoing`), resolving runtime payment insert failures against the `payments.direction` constraint.

1. Closed sales-payment workflow dispatch defect and added end-to-end coverage:

- `FinanceListenerIntegrationTest` now proves `RecordSalesPaymentService` dispatches `SalesPaymentRecorded` and materializes downstream Finance payment/AR posting artifacts.
- `RecordSalesPaymentService` now uses Finance-valid payment direction `inbound` (previously `incoming`), resolving runtime payment insert failures against the `payments.direction` constraint.

1. Closed return-workflow runtime gaps and added end-to-end coverage:

- `FinanceListenerIntegrationTest` now proves `PostPurchaseReturnService` dispatches `PurchaseReturnPosted` and materializes downstream Finance purchase-return journal/AP artifacts.
- The same suite now proves `ReceiveSalesReturnService` dispatches `SalesReturnReceived` and materializes downstream Finance sales-return journal/AR artifacts.
- `EloquentSalesReturnRepository` no longer attempts to persist generated column `sales_return_lines.line_total`, resolving SQLite/runtime insert failures during return-line persistence.
- `HandleSalesReturnReceived` now writes AR transaction type `credit_memo` (schema-valid) instead of `credit_note`, resolving runtime constraint failures in AR posting.

1. Added inventory transfer/costing architecture guardrail coverage:

- `CrossModuleFlowInvariantsTest` now asserts transfer execution and valuation remain internal inventory concerns (stock movement + cost layers + trace logs) and do not implicitly wire Finance listeners.
- The guardrail locks current behavior until an explicit Inventory-to-Finance integration contract is introduced.

1. Added executable runtime proof for transfer-flow finance boundary:

- `InventoryTransferOrderIntegrationTest` now asserts transfer receive persists shipment+receipt stock movements while creating no Finance artifacts (`journal_entries`, `ap_transactions`, `ar_transactions`).
- This complements architecture-level invariants with DB-backed runtime behavior validation.

1. Added executable runtime proof for cycle-count adjustment finance boundary:

- `InventoryCycleCountIntegrationTest` now asserts cycle-count completion posts inventory adjustment movement and trace artifacts while creating no Finance artifacts (`journal_entries`, `ap_transactions`, `ar_transactions`).
- This extends runtime inventory-boundary coverage from transfer receive into adjustment workflows.

1. Added explicit cycle-count-to-finance posting contract and end-to-end coverage:

- `CompleteCycleCountService` now dispatches `CycleCountCompleted` with adjustment payload when variance exists.
- `FinanceServiceProvider` now wires `CycleCountCompleted` to `HandleCycleCountCompleted`.
- `HandleCycleCountCompleted` posts a balanced inventory-adjustment journal entry when product account mappings are present and fiscal period is open.
- `FinanceListenerIntegrationTest` now proves mapped cycle-count adjustments materialize downstream Finance journal artifacts.

1. Added explicit manual stock-adjustment-to-finance posting contract and end-to-end coverage:

- `RecordStockMovementService` now dispatches `StockAdjustmentRecorded` for manual `adjustment_in`/`adjustment_out` movements (excluding cycle-count references to prevent duplicate postings).
- `FinanceServiceProvider` now wires `StockAdjustmentRecorded` to `HandleStockAdjustmentRecorded`.
- `HandleStockAdjustmentRecorded` posts a balanced inventory-adjustment journal entry when product account mappings are present and fiscal period is open.
- `FinanceListenerIntegrationTest` now proves mapped manual stock adjustments materialize downstream Finance journal artifacts.

1. Added executable runtime proof for stock reservation finance boundary:

- `InventoryStockReservationIntegrationTest` now asserts reservation validation failures and reservation release workflows create no Finance artifacts (`journal_entries`, `ap_transactions`, `ar_transactions`).
- This extends runtime inventory-boundary coverage into reservation and expiration-release flows.

1. Added executable runtime proof for direct stock movement finance boundary:

- `InventoryStockMovementIntegrationTest` now asserts direct transfer movement operations create no Finance artifacts (`journal_entries`, `ap_transactions`, `ar_transactions`).
- This completes runtime inventory-boundary proof coverage across transfer receive, cycle count adjustments, reservations, and direct movement APIs.

## 6. Remaining Architectural Gaps (Prioritized)

### High Priority

1. Standardize status modeling in HR

- HR currently uses plain string statuses while other modules largely use constrained enums.
- Impact: weak state governance, possible invalid values, drift in business workflows.
- Recommendation: either (a) enum with strict value set, or (b) controlled lookup tables with FK for extensibility.

1. Consolidate cross-module key naming strategy

- Mixed explicit FK naming vs implicit naming exists.
- Impact: operational confusion during DB troubleshooting and migration diffing.
- Recommendation: enforce one naming convention via architecture test.

1. Ensure consistent indexing strategy for workflow states

- Several tables index `tenant_id` only without state-time composites.
- Impact: degraded performance under high operational load.
- Recommendation: define index templates per table type (document header, line, log, workflow).

### Medium Priority

1. Tighten metadata JSON usage policy

- Metadata columns are widespread; querying expectations are not codified.
- Recommendation: define strict rule for what belongs in JSON vs relational columns.

1. Introduce architecture linting for migration anti-patterns

- Duplicate FK declarations were found by review.
- Recommendation: add static architecture test that detects duplicate FK declaration per column.

## 7. Knowledge Base Standardization Plan

To align with enterprise ERP standards (SAP/Oracle/Dynamics style governance):

1. Create module architecture contract template

- Required sections per module: bounded context, entities, aggregates, invariants, events, external dependencies, and API surface.

1. Add migration quality checklist and CI gate

- Required checks: tenant scoping, PK/FK/UK/index completeness, naming conformance, enum/state policy, rollback safety.

1. Add cross-module data flow catalog

- Canonical flow specs for P2P, O2C, Inventory, Finance close, HR payroll.

1. Add performance SLO maps by module

- Define expected query paths and required indexes per operational screen/use-case.

### Implementation Status Update

- Module architecture contract index created at `docs/architecture/modules/README.md`.
- Standardized module contracts created and populated for all modules under `docs/architecture/modules/*.md` using current repository evidence (entities, migrations, routes, services, events, tests).
- Cross-module runtime sequence catalog published at `docs/architecture/runtime-sequence-matrix.md` with concrete source links and failure-point annotations for P2P, O2C, Inventory valuation, and HR-to-Finance flows.

## 8. Validation Results

Executed after refactors:

- `./vendor/bin/phpunit --filter ModuleBoundaryGuardrailsTest` -> PASS
- `./vendor/bin/phpunit --filter MigrationGuardrailsTest` -> PASS
- `./vendor/bin/phpunit --filter CrossModuleRuntimeFlowGuardrailsTest` -> PASS (4 tests)
- `./vendor/bin/phpunit --filter CrossModuleFlowInvariantsTest` -> PASS (6 tests)
- `./vendor/bin/phpunit --filter CrossModuleFlowInvariantsTest` -> PASS (7 tests)
- `./vendor/bin/phpunit --filter CrossModuleFlowInvariantsTest` -> PASS (8 tests)
- `./vendor/bin/phpunit --filter CreatePaymentServiceTest` -> PASS (4 tests)
- `./vendor/bin/phpunit --filter FinancePaymentIdempotencyIntegrationTest` -> PASS (3 tests)
- `./vendor/bin/phpunit --filter FinanceListenerIntegrationTest` -> PASS (27 tests)
- `./vendor/bin/phpunit --filter FinanceListenerIntegrationTest` -> PASS (28 tests)
- `./vendor/bin/phpunit --filter FinanceRoutesTest` -> PASS
- `./vendor/bin/phpunit --filter HRRoutesTest` -> PASS
- `./vendor/bin/phpunit --filter ConfigurationModuleMigrationSmokeTest` -> PASS
- `./vendor/bin/phpunit --filter InventoryTransferOrderIntegrationTest` -> PASS (1 test)
- `./vendor/bin/phpunit --filter InventoryCycleCountIntegrationTest` -> PASS (1 test)
- `./vendor/bin/phpunit --filter InventoryStockReservationIntegrationTest` -> PASS (3 tests)
- `./vendor/bin/phpunit --filter InventoryStockMovementIntegrationTest` -> PASS (2 tests)
- `./vendor/bin/phpunit tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php` -> PASS (9 tests, 107 assertions)
- `./vendor/bin/phpunit tests/Feature/FinanceListenerIntegrationTest.php` -> PASS (29 tests, 123 assertions)
- `./vendor/bin/phpunit tests/Feature/FinanceListenerIntegrationTest.php tests/Feature/InventoryTransferOrderIntegrationTest.php tests/Feature/InventoryCycleCountIntegrationTest.php tests/Feature/InventoryStockReservationIntegrationTest.php tests/Feature/InventoryStockMovementIntegrationTest.php tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php` -> PASS (45 tests, 287 assertions)
- `./vendor/bin/phpunit tests/Feature/FinanceListenerIntegrationTest.php tests/Unit/Finance/CreatePaymentServiceTest.php tests/Feature/FinancePaymentIdempotencyIntegrationTest.php tests/Unit/Architecture/MigrationGuardrailsTest.php tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php tests/Unit/Architecture/CrossModuleRuntimeFlowGuardrailsTest.php tests/Feature/FinanceRoutesTest.php tests/Feature/ConfigurationModuleMigrationSmokeTest.php tests/Feature/InventoryTransferOrderIntegrationTest.php tests/Feature/InventoryCycleCountIntegrationTest.php tests/Feature/InventoryStockReservationIntegrationTest.php tests/Feature/InventoryStockMovementIntegrationTest.php` -> PASS (60 tests, 368 assertions)
- `./vendor/bin/phpunit tests/Feature/FinanceListenerIntegrationTest.php tests/Unit/Finance/CreatePaymentServiceTest.php tests/Feature/FinancePaymentIdempotencyIntegrationTest.php tests/Unit/Architecture/MigrationGuardrailsTest.php tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php tests/Unit/Architecture/CrossModuleRuntimeFlowGuardrailsTest.php tests/Feature/FinanceRoutesTest.php tests/Feature/ConfigurationModuleMigrationSmokeTest.php tests/Feature/InventoryTransferOrderIntegrationTest.php tests/Feature/InventoryCycleCountIntegrationTest.php tests/Feature/InventoryStockReservationIntegrationTest.php tests/Feature/InventoryStockMovementIntegrationTest.php` -> PASS (62 tests, 384 assertions)

Guardrail automation added:

- `tests/Unit/Architecture/MigrationGuardrailsTest.php` — enforces no duplicate FK declarations; presence of operational composite indexes on high-traffic tables.
- `tests/Unit/Architecture/CrossModuleRuntimeFlowGuardrailsTest.php` — locks runtime-sequence-matrix.md to real code artifacts (4 tests).
- `tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php` — asserts complete event-chain artifacts for P2P, O2C, HR-to-Finance, and tenant propagation (6 tests):
- `tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php` — asserts complete event-chain artifacts for P2P, O2C, HR-to-Finance, tenant propagation, and inventory transfer/costing boundary invariants (7 tests):
- `tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php` — asserts complete event-chain artifacts for P2P, O2C, HR-to-Finance, tenant propagation, inventory transfer/costing boundary invariants, and cycle-count-to-finance wiring contract (8 tests):
- `tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php` — asserts complete event-chain artifacts for P2P, O2C, HR-to-Finance, tenant propagation, inventory transfer/costing boundary invariants, cycle-count-to-finance wiring contract, and manual stock-adjustment-to-finance wiring contract (9 tests):
  - P2P: ConfirmPurchaseOrderService → GoodsReceiptPosted → PurchaseInvoiceApproved → Finance/Inventory listeners
  - O2C: ProcessShipmentService → SalesInvoicePosted → Finance listener
  - HR-to-Finance: PayrollRunApproved dispatch, Payslip.journal_entry_id FK contract, ProcessPayrollRunService initialisation
  - Finance listener wiring: PayrollRunApproved → HandlePayrollRunApproved
  - Tenant propagation: Finance and Inventory listeners must read $event->tenantId
  - Inventory transfer/costing boundary: transfer receipt must record shipment+receipt stock movements, valuation must stay cost-layer based, and Finance provider must not implicitly wire transfer/stock/cost listeners
  - Inventory-to-Finance cycle count contract: CompleteCycleCountService dispatches CycleCountCompleted and Finance provider/listener chain is wired
  - Inventory-to-Finance manual stock-adjustment contract: RecordStockMovementService dispatches StockAdjustmentRecorded and Finance provider/listener chain is wired
- `tests/Unit/Finance/CreatePaymentServiceTest.php` — verifies payment replay-safe create behavior and duplicate-insert recovery (4 tests):
  - Returns existing persisted payment when `idempotency_key` matches within tenant scope
  - Persists a new payment when the `idempotency_key` is unseen
  - Skips idempotency lookup when the key is absent
  - Re-reads and returns the winning payment row when the save path hits an idempotency unique-key race
- `tests/Feature/FinancePaymentIdempotencyIntegrationTest.php` — validates DB-backed payment replay semantics (3 tests):
  - Same-tenant repeated create with the same `idempotency_key` returns the original row and does not persist a duplicate
  - The same `idempotency_key` can be reused safely across different tenants
  - Requests without an `idempotency_key` still persist distinct rows
- `tests/Feature/FinanceListenerIntegrationTest.php` — validates listener-driven Finance postings and upstream workflow dispatch into Finance artifacts across purchase, sales, return, payment, payroll, cycle-count adjustment, and manual stock-adjustment flows (29 tests):
  - Purchase invoice approval posts AP journal impact
  - Sales invoice posting creates AR journal impact
  - Purchase payment recording posts cash/AP effects and AP transactions
  - Sales payment recording posts cash/AR effects and AR transactions
  - Payroll run approval posts payroll expense, net liability, and deductions liability with skip-path coverage for invalid runtime conditions
  - ApprovePayrollRunService dispatches the payroll approval event and produces the downstream Finance journal artifact
  - ApprovePurchaseInvoiceService dispatches purchase-invoice approval into downstream Finance posting
  - PostSalesInvoiceService dispatches sales-invoice posting into downstream Finance posting
  - RecordPurchasePaymentService dispatches purchase-payment recording into downstream Finance posting
  - RecordSalesPaymentService dispatches sales-payment recording into downstream Finance posting
  - PostPurchaseReturnService dispatches purchase-return posting into downstream Finance posting
  - ReceiveSalesReturnService dispatches sales-return receipt into downstream Finance posting
  - CompleteCycleCountService dispatches cycle-count completion into downstream Finance posting when account mappings are present
  - RecordStockMovementService dispatches manual stock-adjustment events into downstream Finance posting when account mappings are present
- `tests/Feature/InventoryTransferOrderIntegrationTest.php` — validates transfer order create/approve/receive stock behavior and now locks the no-finance-side-effects runtime contract for transfer receive (1 test)
- `tests/Feature/InventoryCycleCountIntegrationTest.php` — validates cycle count completion inventory adjustments and now locks no-finance-side-effects behavior when product account mappings are absent (1 test)
- `tests/Feature/InventoryStockReservationIntegrationTest.php` — validates stock reservation/release semantics and now locks the no-finance-side-effects runtime contract for reservation and expiration-release flows (3 tests)
- `tests/Feature/InventoryStockMovementIntegrationTest.php` — validates direct stock movement persistence and now locks the no-finance-side-effects runtime contract for movement API paths (2 tests)

## 9. Recommended Next Refactor Wave

1. Normalize HR status strategy across all HR transactional tables.
2. Extend explicit inventory-to-finance posting contracts beyond cycle counts and manual stock adjustments (for example selected transfer/costing scenarios) with event/listener-level integration tests.
3. Enforce migration naming/index policies via additional architecture tests (FK naming uniformity + workflow index templates).
4. Add true multi-request or worker-level payment replay coverage so the unique-key recovery path is validated under real concurrent execution, not only simulated collision handling.
