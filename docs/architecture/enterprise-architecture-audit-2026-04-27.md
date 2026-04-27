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
|---|---|---|---|---|
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

2. Order-to-Cash (O2C)
- `sales_orders` -> `shipments`/`shipment_lines` -> `sales_invoices` -> `payments`/`ar_transactions` -> `journal_entries`.

3. Inventory Movements and Valuation
- `stock_movements`, `stock_levels`, `inventory_cost_layers`, reservations, and transfer orders.
- Product identifiers, serials, and batches provide traceability dimensions.

4. HR to Finance
- Payroll runs and payslips can post to finance (`journal_entries`) for accrual and settlement scenarios.

## 5. Defects and Refactors Applied

The following migration issues were fixed directly (initial development phase policy: modify existing migrations):

1. Removed duplicate foreign key definitions on already-constrained columns:
- `app/Modules/OrganizationUnit/database/migrations/2024_01_01_200003_create_org_unit_attachments_table.php`
- `app/Modules/Purchase/database/migrations/2024_01_01_100001_create_purchase_orders_table.php`
- `app/Modules/Purchase/database/migrations/2024_01_01_100002_create_purchase_order_lines_table.php`

2. Fixed migration formatting inconsistency affecting readability/maintainability:
- `app/Modules/OrganizationUnit/database/migrations/2024_01_01_200003_create_org_unit_attachments_table.php`

3. Added operational indexes for high-frequency status/date query paths:
- `app/Modules/Finance/database/migrations/2024_01_01_120005b_create_payments_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900006_create_hr_leave_requests_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900010_create_hr_payroll_runs_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900012_create_hr_payslips_table.php`
- `app/Modules/HR/database/migrations/2024_01_01_900015_create_hr_performance_reviews_table.php`

4. Added missing referential constraints for integrity and consistency:
- `hr_leave_requests.approver_id -> users.id`
- `hr_payroll_runs.approved_by -> users.id`
- `hr_payslips.journal_entry_id -> journal_entries.id`
- `hr_performance_reviews.reviewer_id -> users.id`

## 6. Remaining Architectural Gaps (Prioritized)

### High Priority

1. Standardize status modeling in HR
- HR currently uses plain string statuses while other modules largely use constrained enums.
- Impact: weak state governance, possible invalid values, drift in business workflows.
- Recommendation: either (a) enum with strict value set, or (b) controlled lookup tables with FK for extensibility.

2. Consolidate cross-module key naming strategy
- Mixed explicit FK naming vs implicit naming exists.
- Impact: operational confusion during DB troubleshooting and migration diffing.
- Recommendation: enforce one naming convention via architecture test.

3. Ensure consistent indexing strategy for workflow states
- Several tables index `tenant_id` only without state-time composites.
- Impact: degraded performance under high operational load.
- Recommendation: define index templates per table type (document header, line, log, workflow).

### Medium Priority

4. Tighten metadata JSON usage policy
- Metadata columns are widespread; querying expectations are not codified.
- Recommendation: define strict rule for what belongs in JSON vs relational columns.

5. Introduce architecture linting for migration anti-patterns
- Duplicate FK declarations were found by review.
- Recommendation: add static architecture test that detects duplicate FK declaration per column.

## 7. Knowledge Base Standardization Plan

To align with enterprise ERP standards (SAP/Oracle/Dynamics style governance):

1. Create module architecture contract template
- Required sections per module: bounded context, entities, aggregates, invariants, events, external dependencies, and API surface.

2. Add migration quality checklist and CI gate
- Required checks: tenant scoping, PK/FK/UK/index completeness, naming conformance, enum/state policy, rollback safety.

3. Add cross-module data flow catalog
- Canonical flow specs for P2P, O2C, Inventory, Finance close, HR payroll.

4. Add performance SLO maps by module
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
- `./vendor/bin/phpunit --filter FinanceRoutesTest` -> PASS
- `./vendor/bin/phpunit --filter HRRoutesTest` -> PASS
- `./vendor/bin/phpunit --filter ConfigurationModuleMigrationSmokeTest` -> PASS

Guardrail automation added:
- `tests/Unit/Architecture/MigrationGuardrailsTest.php` — enforces no duplicate FK declarations; presence of operational composite indexes on high-traffic tables.
- `tests/Unit/Architecture/CrossModuleRuntimeFlowGuardrailsTest.php` — locks runtime-sequence-matrix.md to real code artifacts (4 tests).
- `tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php` — asserts complete event-chain artifacts for P2P, O2C, HR-to-Finance, and tenant propagation (6 tests):
  - P2P: ConfirmPurchaseOrderService → GoodsReceiptPosted → PurchaseInvoiceApproved → Finance/Inventory listeners
  - O2C: ProcessShipmentService → SalesInvoicePosted → Finance listener
  - HR-to-Finance: PayrollRunApproved dispatch, Payslip.journal_entry_id FK contract, ProcessPayrollRunService initialisation
  - Regression sentinel: Finance has no PayrollRunApproved listener yet (known open debt)
  - Tenant propagation: Finance and Inventory listeners must read $event->tenantId

## 9. Recommended Next Refactor Wave

1. Implement Finance listener for `PayrollRunApproved` to close HR-to-Finance posting gap (removes regression sentinel in `CrossModuleFlowInvariantsTest`).
2. Add `idempotency_key` / unique constraint to `payments` table to prevent double-payment on replay.
3. Normalize HR status strategy across all HR transactional tables.
4. Add integration tests for cross-module posting consistency (Sales/Purchase/Inventory → Finance).
