# Repository and Runtime Architecture Audit (2026-04-29)

## 1. Scope

This audit reviewed:

- All modules under `app/Modules/*`.
- All repository implementations under `app/Modules/*/Infrastructure/Persistence/Eloquent/Repositories/*`.
- High-risk runtime integrations via cross-module event listeners.
- Migration/model/repository alignment with tenant-scope and clean-architecture constraints.

## 2. Validation Method

- Structural inventory using module-level layer/repository/model/migration/event counts.
- Static repository scan for correctness, missing functionality, and anti-patterns.
- Compile diagnostics for concrete defects in repository code.
- Focused runtime validation using HR tests after repository refactor.

## 3. Module Inventory Snapshot

| Module | Domain | Application | Infrastructure | Repositories | Models | Migrations | Events |
| --- | --- | --- | --- | ---: | ---: | ---: | ---: |
| Audit | Yes | Yes | Yes | 1 | 1 | 1 | 0 |
| Auth | Yes | Yes | Yes | 0 | 0 | 0 | 3 |
| Configuration | Yes | Yes | Yes | 4 | 4 | 4 | 0 |
| Core | Yes | Yes | Yes | 0 | 1 | 0 | 2 |
| Customer | Yes | Yes | Yes | 3 | 3 | 3 | 0 |
| Employee | Yes | Yes | Yes | 1 | 1 | 1 | 0 |
| Finance | Yes | Yes | Yes | 19 | 20 | 20 | 0 |
| HR | Yes | Yes | Yes | 15 | 16 | 16 | 7 |
| Inventory | Yes | Yes | Yes | 7 | 12 | 16 | 3 |
| OrganizationUnit | Yes | Yes | Yes | 4 | 4 | 4 | 0 |
| Pricing | Yes | Yes | Yes | 4 | 4 | 4 | 0 |
| Product | Yes | Yes | Yes | 12 | 12 | 13 | 0 |
| Purchase | Yes | Yes | Yes | 8 | 8 | 8 | 5 |
| Sales | Yes | Yes | Yes | 4 | 8 | 8 | 4 |
| Shared | No | No | Yes | 0 | 0 | 0 | 0 |
| Supplier | Yes | Yes | Yes | 4 | 4 | 4 | 0 |
| Tax | Yes | Yes | Yes | 4 | 4 | 4 | 0 |
| Tenant | Yes | Yes | Yes | 5 | 5 | 5 | 13 |
| User | Yes | Yes | Yes | 5 | 5 | 8 | 6 |
| Warehouse | Yes | Yes | Yes | 2 | 2 | 2 | 0 |

## 4. Runtime Integration Flows (Observed)

### 4.1 Finance as posting sink

- Finance consumes events from HR, Inventory, Purchase, and Sales listeners.
- This confirms Finance as the central accounting projection boundary.

### 4.2 Inventory as stock-state sink

- Inventory consumes Purchase and Sales fulfillment/return events.
- This confirms Inventory as stock movement and valuation projection boundary.

### 4.3 High-value real-time paths

- Procure-to-Pay: Purchase -> Inventory + Finance.
- Order-to-Cash: Sales -> Inventory + Finance.
- HR payroll posting: HR -> Finance.
- Cycle count/stock adjustments: Inventory -> Finance.

## 5. Findings

### 5.1 Strengths

- Clean-architecture layering exists in all implemented business modules.
- Tenant-first schema/repository conventions are widely followed.
- Cross-module coupling is predominantly event-driven at runtime.
- Existing architecture docs already provide strong coverage and governance direction.

### 5.2 Gaps and risks

- Repository consistency is uneven across modules (query style, eager-loading discipline, write synchronization patterns).
- Some repositories still include local anti-patterns that can lead to stale child collections or inefficient N+1 reads.
- Finance/Inventory/Purchase/Sales remain the most sensitive areas for concurrency and replay safety due to event-driven projections.
- Shared module remains intentionally thin; this boundary should be protected continuously to avoid domain leakage.

## 6. Concrete Repository Fixes Applied

### 6.1 HR payslip repository hardening

File: `app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentPayslipRepository.php`

Applied changes:

- Fixed typed mapping issue in `findByPayrollRun` by using typed model mapping.
- Added eager loading (`with('lines')`) to `find`, `findByEmployeeAndRun`, and `findByPayrollRun` to remove N+1 line fetch patterns.
- Wrapped `save` in a DB transaction for atomic header + line persistence.
- Added tenant-safe upsert key for payslip lines (`tenant_id + payslip_id + item_code`).
- Added orphan line cleanup to keep persisted lines synchronized with aggregate payload.

Why this matters:

- Prevents stale or drifted payslip line sets after updates.
- Improves runtime consistency and read performance.
- Aligns with aggregate persistence best practices in clean architecture.

## 7. Validation Results

Focused tests executed after repository refactor:

- `tests/Feature/HrRoutesTest.php`
- `tests/Feature/PayrollRunServiceTest.php`
- `tests/Feature/HrFinanceIntegrationTest.php`

Result: 9 passed, 0 failed.

Additional focused validation after Sales aggregate repository refactor:

- `tests/Feature/SalesRoutesTest.php`
- `tests/Feature/SalesOrderRepositoryIntegrationTest.php`

Result: 15 passed, 0 failed.

Additional focused validation after Purchase tenant-safety refactor:

- `tests/Feature/PurchaseRoutesTest.php`
- `tests/Feature/PurchaseOrderRepositoryIntegrationTest.php`

Result: 12 passed, 0 failed.

Additional focused validation after Inventory line-update optimization:

- `tests/Feature/InventoryTransferOrderIntegrationTest.php`
- `tests/Feature/InventoryTransferOrderRoutesTest.php`

Result: 4 passed, 0 failed.

Additional focused validation after Finance invoice-listener replay hardening:

- `tests/Feature/FinanceListenerIntegrationTest.php::test_handle_purchase_invoice_approved_duplicate_event_is_replay_safe`
- `tests/Feature/FinanceListenerIntegrationTest.php::test_handle_purchase_invoice_approved_duplicate_conflict_with_partial_artifacts_throws`
- `tests/Feature/FinanceListenerIntegrationTest.php::test_handle_sales_invoice_posted_duplicate_event_is_replay_safe`

Result: 3 passed, 0 failed.

Additional focused validation after Finance inventory/payroll listener replay hardening:

- `tests/Feature/FinanceListenerIntegrationTest.php::test_handle_cycle_count_completed_duplicate_event_is_replay_safe`
- `tests/Feature/FinanceListenerIntegrationTest.php::test_handle_stock_adjustment_recorded_duplicate_event_is_replay_safe`
- `tests/Feature/FinanceListenerIntegrationTest.php::test_handle_payroll_run_approved_duplicate_event_is_replay_safe`

Result: 3 passed, 0 failed.

Additional focused validation after architecture guardrail reinforcement:

- `tests/Unit/Architecture/SharedModuleGuardrailsTest.php`
- `tests/Unit/Architecture/FinanceListenerReplayGuardrailsTest.php`

Result: 4 passed, 0 failed.

Additional focused validation after migration/index guardrail reinforcement:

- `tests/Unit/Architecture/MigrationGuardrailsTest.php`

Result: 8 passed, 0 failed.

Additional focused validation after replay-contract architecture parity expansion:

- `tests/Unit/Architecture/EventReplayGuardrailsTest.php`

Result: 2 passed, 0 failed.

Additional focused validation after repository infrastructure tenant-scope hardening:

- `tests/Unit/Architecture/RepositoryInfrastructureGuardrailsTest.php`

Result: 3 passed, 0 failed.

Supplemental focused validation after aggregate repository write guardrail expansion:

- `tests/Unit/Architecture/AggregateRepositoryWriteGuardrailsTest.php`
- `tests/Unit/Architecture/RepositoryInfrastructureGuardrailsTest.php`

Result: 6 passed, 0 failed.

Supplemental focused validation after cross-tenant mutation behavior coverage expansion:

- `tests/Feature/SalesOrderRepositoryIntegrationTest.php`
- `tests/Feature/PurchaseOrderRepositoryIntegrationTest.php`
- `tests/Feature/InventoryTransferOrderIntegrationTest.php`
- `tests/Feature/HRAttendanceRecordIntegrationTest.php`

Result: 19 passed, 0 failed.

Supplemental focused validation after cross-tenant cancel/delete behavior coverage expansion:

- `tests/Feature/SalesOrderRepositoryIntegrationTest.php`
- `tests/Feature/PurchaseOrderRepositoryIntegrationTest.php`

Result: 19 passed, 0 failed.

Combined focused validation in this audit cycle: 250 passed, 0 failed.

Finance listener integration suite sanity run:

- `tests/Feature/FinanceListenerIntegrationTest.php`

Result: 48 passed, 0 failed.

Full suite regression run after tenant-isolation behavior expansion:

- `./vendor/bin/phpunit`

Result: 614 passed, 0 failed.

## 8. Enterprise Alignment Notes (SAP/Oracle/D365 style)

Current direction is aligned with enterprise ERP expectations in these areas:

- Bounded contexts per module.
- Event-driven cross-module projections.
- Tenant partitioning and explicit ownership boundaries.
- Accounting/inventory flow separation with integration through events.

To improve long-term maintainability and extensibility:

1. Standardize repository aggregate-sync patterns (header/line aggregates) across Purchase, Sales, HR, and Inventory.
2. Add explicit replay/idempotency invariant tests for all event listeners that post financial artifacts.
3. Add schema guardrail tests for critical composite indexes and tenant/org-unit scoped unique constraints.
4. Keep Shared as a shell-only module and block business-logic creep with architecture tests.

## 9. Next Priority Backlog

1. Repository pattern normalization pass for aggregate roots in Purchase/Sales/Inventory to mirror the payslip fix.
2. Add listener replay/inconsistency tests for all Finance posting listeners.
3. Add query-shape profiling and index guardrails for Warehouse path and high-volume Inventory stock movement queries.
4. Add module-level architecture tests that lock critical cross-module contract assumptions.

## 10. Additional Refactors Applied (Continuation)

### 10.1 Sales aggregate line synchronization hardening

Files:

- `app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesOrderRepository.php`
- `app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesInvoiceRepository.php`
- `app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentShipmentRepository.php`

Applied changes:

- Replaced full line delete-and-recreate writes with id-aware line synchronization.
- Updated existing lines in place when line IDs are present.
- Inserted new lines only when not matched.
- Pruned removed lines using tenant-scoped cleanup (`whereNotIn` by kept IDs).
- Kept writes transactional to preserve aggregate consistency.

Why this matters:

- Reduces write amplification and row churn.
- Preserves line-row continuity for auditing and downstream references.
- Improves behavior under concurrent retries compared with blanket line replacement.

### 10.2 Purchase line repository tenant-safety hardening

Files:

- `app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseOrderLineRepository.php`
- `app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseInvoiceLineRepository.php`
- `app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentGrnLineRepository.php`
- `app/Modules/Purchase/Application/Services/ApprovePurchaseInvoiceService.php`
- `app/Modules/Purchase/Application/Services/PostGrnService.php`
- `app/Modules/Purchase/Application/Services/PostPurchaseReturnService.php`

Applied changes:

- Updated line-list repository contract methods to require `tenantId`.
- Added tenant filters to line list queries for purchase order, invoice, and GRN lines.
- Updated all application service call sites to pass tenant-aware query inputs.
- Added tenant consistency guard before applying GRN-driven purchase-order-line quantity updates.

Why this matters:

- Prevents cross-tenant data exposure through ID-based line retrieval.
- Makes line retrieval semantics explicit and consistent with module tenancy rules.
- Hardens high-value P2P posting paths that rely on these line collections.

### 10.3 Inventory line-update performance hardening

Files:

- `app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentTransferOrderRepository.php`
- `app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCycleCountRepository.php`

Applied changes:

- Replaced repeated `firstWhere` scans with one-time `keyBy('id')` indexing of loaded line collections.
- Added explicit integer parsing for incoming `line_id` values before lookups.

Why this matters:

- Reduces per-request line update complexity from repeated linear scans to indexed lookups.
- Improves runtime efficiency for larger transfer and cycle-count batches.
- Preserves existing domain behavior while hardening high-volume inventory paths.

### 10.4 Shared repository infrastructure tenant-scope hardening

Files:

- `app/Modules/Core/Infrastructure/Persistence/Repositories/EloquentRepository.php`
- `app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserDeviceRepository.php`

Applied changes:

- Added centralized `newScopedQuery()` construction in the shared Eloquent repository base.
- Applied automatic tenant scoping for models that expose a `tenant_id` column when current tenant context is bound.
- Reused scoped query construction in provider reset and raw model lookup paths.
- Added cached tenant-column detection to avoid repeated schema inspection per table.
- Removed direct `Auth`/`Request` facade usage from `EloquentUserDeviceRepository` and switched it to the shared tenant-context resolver.
- Standardized domain mapping in `EloquentUserDeviceRepository::find()` through the base repository mapper.

Why this matters:

- Moves tenant-context resolution out of individual repositories and into shared infrastructure.
- Reduces duplication and repository-layer transport coupling.
- Hardens default `find`/`update`/`delete` paths against accidental cross-tenant access for tenant-owned models.
- Establishes a reusable base pattern that future repositories inherit automatically.

### 10.5 New architecture guardrails added

Files:

- `tests/Unit/Architecture/RepositoryInfrastructureGuardrailsTest.php`

Applied changes:

- Added a guardrail that locks the presence of centralized tenant-scoped query construction in the shared Eloquent base repository.
- Added a repository-layer boundary check to prevent `Auth` and `Request` facade usage inside Eloquent repository implementations.
- Added a targeted guardrail confirming `EloquentUserDeviceRepository` uses the shared tenant-context resolver instead of local transport-coupled resolution.

Why this matters:

- Converts the repository cleanup from a one-time refactor into an enforced architectural invariant.
- Protects separation of concerns in future repository changes.
- Keeps tenancy behavior consistent without broad, brittle assertions across unrelated layers.

### 10.4 Finance invoice-listener replay hardening

Files:

- `app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php`
- `tests/Feature/FinanceListenerIntegrationTest.php`

Applied changes:

- Added pre-flight replay checks before invoice posting side effects execute.
- Added duplicate-key (`QueryException`) replay-conflict handling.
- For purchase invoices, enforced strict paired-artifact verification (`journal_entries` + `ap_transactions`) and explicit inconsistency failure on partial states.
- Added focused integration tests for duplicate replay-safe behavior and purchase-invoice partial-artifact conflict failure.

Why this matters:

- Prevents duplicate finance artifacts when invoice events are retried.
- Preserves exactly-once semantics for purchase-invoice postings under contention/retries.
- Ensures partial posting states fail loudly rather than being silently accepted.

### 10.5 Finance inventory/payroll listener replay hardening

Files:

- `app/Modules/Finance/Infrastructure/Listeners/HandleCycleCountCompleted.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandleStockAdjustmentRecorded.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandlePayrollRunApproved.php`
- `tests/Feature/FinanceListenerIntegrationTest.php`

Applied changes:

- Added pre-flight replay checks before journal posting side effects execute for cycle count, stock adjustment, and payroll run listeners.
- Added duplicate-key (`QueryException`) replay-conflict handling for journal uniqueness collisions.
- Added strict post-conflict verification that a journal artifact exists; if not, listener now throws an explicit inconsistency exception.
- Added focused duplicate replay-safe tests for each hardened listener.

Why this matters:

- Extends idempotent replay semantics across all major Finance journal-only posting listeners.
- Reduces duplicate journal risk under at-least-once event delivery and concurrent retries.
- Makes partial/inconsistent replay conflict states visible instead of silently masking them.

### 10.6 Finance replay-helper consolidation

Files:

- `app/Modules/Finance/Infrastructure/Listeners/Concerns/HandlesReplayConflicts.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseInvoiceApproved.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandleSalesInvoicePosted.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandlePurchasePaymentRecorded.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandleSalesPaymentRecorded.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandlePurchaseReturnPosted.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandleSalesReturnReceived.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandleCycleCountCompleted.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandleStockAdjustmentRecorded.php`
- `app/Modules/Finance/Infrastructure/Listeners/HandlePayrollRunApproved.php`

Applied changes:

- Extracted shared replay helper methods into `HandlesReplayConflicts` trait:
  - journal artifact existence check
  - paired artifact existence check for transaction tables
  - duplicate/unique replay conflict detection with extensible constraint hints
- Migrated all Finance listeners that enforce replay safety to use the shared trait.
- Removed duplicated private replay helper methods from each listener.

Why this matters:

- Keeps replay semantics uniform across Finance posting paths.
- Reduces maintenance drift when replay constraints evolve.
- Preserves behavior while simplifying listener implementations.

### 10.7 Architecture guardrail reinforcement

Files:

- `tests/Unit/Architecture/SharedModuleGuardrailsTest.php`
- `tests/Unit/Architecture/FinanceListenerReplayGuardrailsTest.php`

Applied changes:

- Added an explicit Shared module top-level structure assertion to lock shell-only scope (`Infrastructure` + `routes` only).
- Added replay listener guardrails that enforce:
  - all replay-hardened Finance listeners import and use `HandlesReplayConflicts`
  - no listener reintroduces local replay helper methods (`journalAlreadyPosted`, `artifactsAlreadyPosted`, `isReplayConflict`)

Why this matters:

- Prevents accidental expansion of Shared into a business-logic module.
- Locks replay consistency patterns introduced in Finance listener hardening.
- Converts architectural conventions into executable, regression-resistant checks.

### 10.8 Migration/index guardrail reinforcement

Files:

- `app/Modules/Inventory/database/migrations/2024_01_01_900010a_create_cycle_count_headers_table.php`
- `app/Modules/Inventory/database/migrations/2024_01_01_900008a_create_stock_adjustments_table.php`
- `tests/Unit/Architecture/MigrationGuardrailsTest.php`

Applied changes:

- Added missing operational status indexes for high-traffic Inventory workflows:
  - `cycle_count_headers_tenant_status_idx`
  - `stock_adjustments_tenant_status_idx`
- Extended migration guardrail assertions to enforce status-operational indexes for:
  - transfer orders
  - cycle count headers
  - stock adjustments

Why this matters:

- Aligns Inventory workflow tables with status-driven query patterns used in services and endpoints.
- Reduces regression risk where future migration edits silently drop operational indexes.
- Keeps performance guardrails executable and tenant-aware.

### 10.9 Replay-contract architecture parity expansion

Files:

- `tests/Unit/Architecture/EventReplayGuardrailsTest.php`

Applied changes:

- Extended artifact-pair replay guardrail assertions to include purchase invoice posting listener contracts.
- Added journal-only replay guardrail assertions for:
  - sales invoice posting
  - payroll run approval
  - cycle count completion
  - stock adjustment recording
- Locked each listener contract by asserting replay pre-check usage, reference-type anchors, replay-skip log signatures, and explicit inconsistency failure messages.

Why this matters:

- Keeps replay expectations executable across all replay-hardened Finance posting paths.
- Prevents drift between implementation and architecture guardrails as listener coverage expands.
- Complements trait-usage guardrails with behavior-contract guardrails at source level.

---

### 10.10 Purchase line-list repository tenant-filter enforcement

Files changed:

- `app/Modules/Purchase/Domain/RepositoryInterfaces/PurchaseReturnLineRepositoryInterface.php`
- `app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseReturnLineRepository.php`
- `app/Modules/Purchase/Application/Services/PostPurchaseReturnService.php`

New guardrail test:

- `tests/Unit/Architecture/PurchaseLineRepositoryGuardrailsTest.php`

Applied changes:

- Found `PurchaseReturnLineRepositoryInterface::findByPurchaseReturnId` was the only
  remaining Purchase line-list method without an explicit `tenantId` first parameter.
  All three sibling interfaces (`PurchaseOrderLine`, `PurchaseInvoiceLine`, `GrnLine`)
  had already been hardened in prior waves.
- Added `int $tenantId` as the first parameter to the interface method, the Eloquent
  implementation, and the single call site in `PostPurchaseReturnService`.
- Implementation now applies `->where('tenant_id', $tenantId)` before
  `->where('purchase_return_id', $purchaseReturnId)`, preventing cross-tenant line leaks
  when return IDs collide across tenants.
- Created `PurchaseLineRepositoryGuardrailsTest` with three test groups:
  1. All four line-list interface methods declare `tenantId` as `int` first param.
  2. All four Eloquent implementation methods honour the same signature.
  3. All four implementation method bodies reference `tenant_id` in the query.

Why this matters:

- A parent-entity ID (purchase_return_id) is only unique within a tenant, not globally.
  Querying without scoping to `tenant_id` first could return another tenant's lines in
  a shared-database multi-tenant deployment.
- The architecture guardrail locks this contract at reflection level so a future
  refactor cannot silently drop the tenant filter.

---

### 10.11 Warehouse and Inventory valuation path operational index coverage

Files changed (migrations):

- `app/Modules/Warehouse/database/migrations/2024_01_01_800001_create_warehouses_table.php`
  — added `warehouses_tenant_active_idx` `(tenant_id, is_active)`
- `app/Modules/Warehouse/database/migrations/2024_01_01_800002_create_warehouse_locations_table.php`
  — added `warehouse_locations_tenant_warehouse_idx` `(tenant_id, warehouse_id)`
- `app/Modules/Inventory/database/migrations/2024_01_01_900007a_create_stock_transfers_table.php`
  — added `stock_transfers_tenant_status_idx` `(tenant_id, status)`
- `app/Modules/Inventory/database/migrations/2024_01_01_900005_create_inventory_cost_layers_table.php`
  — added `inventory_cost_layers_tenant_product_open_idx` `(tenant_id, product_id, is_closed)`

Guardrail test extended:

- `tests/Unit/Architecture/MigrationGuardrailsTest.php`
  — new method `test_warehouse_and_inventory_valuation_paths_keep_operational_indexes`

Applied changes:

- `warehouses` had only a composite unique key, no listing query index. The most common
  runtime query is listing active warehouses per tenant — added `(tenant_id, is_active)`.
- `warehouse_locations` had a parent-hierarchy index but no flat warehouse-member index.
  The most common listing query is all locations under a warehouse — added
  `(tenant_id, warehouse_id)`.
- `stock_transfers` had only the reference unique key, no operational status filter index.

### 10.12 Cross-tenant mutation behavior coverage expansion

Files changed:

- `tests/Feature/SalesOrderRepositoryIntegrationTest.php`
- `tests/Feature/PurchaseOrderRepositoryIntegrationTest.php`
- `tests/Feature/InventoryTransferOrderIntegrationTest.php`
- `tests/Feature/HRAttendanceRecordIntegrationTest.php`

Applied changes:

- Added service-level cross-tenant mutation tests for Sales order updates and Purchase order updates by binding a different current tenant context and asserting the update path fails as not found.
- Added service-level cross-tenant cancel/delete tests for Sales order cancellation and Purchase order deletion using the same tenant-scoped lookup boundary.
- Added transfer-order receipt coverage to prove Inventory workflow mutations reject the wrong tenant and do not materialize foreign-tenant stock movements.
- Added HR attendance update coverage to prove tenant-scoped repository lookup blocks cross-tenant record mutation and leaves persisted state unchanged.
- Kept all assertions state-based: each test verifies the original tenant-owned row remains unchanged after the rejected mutation attempt.

Why this matters:

- Complements source-level repository and architecture guardrails with executable behavioral evidence.
- Validates the shared `EloquentRepository` tenant-scoped lookup hardening through real application-service flows instead of only source inspection.
- Reduces regression risk on the highest-value aggregate and workflow mutation paths across Sales, Purchase, Inventory, and HR.
  Transfer pipeline queries (pending, in_transit) scatter across all tenants without a
  status index — added `(tenant_id, status)`.
- `inventory_cost_layers` had a date-ordered index for layer history queries but no
  open-layer filter index. FIFO/FEFO valuation queries exclusively read layers where
  `is_closed = false`; without a dedicated index this becomes a full tenant+product scan
  — added `(tenant_id, product_id, is_closed)`.

---

### 10.12 Aggregate repository write-guardrail coverage

Files:

- `tests/Unit/Architecture/AggregateRepositoryWriteGuardrailsTest.php`

Applied changes:

- Added guardrails for Sales aggregate repositories to lock transactional save behavior,
  shared scoped update delegation, tenant-scoped child writes, explicit child pruning,
  and post-save eager reload of line collections.
- Added a dedicated payslip repository guardrail to preserve transactional line upsert
  behavior and tenant-scoped cleanup in HR payroll persistence.
- Added Purchase header repository guardrails to ensure header-only repositories continue
  routing ID-based writes through the shared scoped base repository.

Why this matters:

- Protects the highest-risk aggregate write paths from drifting back to non-transactional
  or cross-tenant-unsafe persistence behavior.
- Keeps write-side repository conventions executable rather than relying on audit notes alone.
- Reinforces the distinction between aggregate-sync repositories and header-only repositories.

### 10.13 Repository knowledge-base expansion

Files:

- `docs/architecture/repository-module-matrix-2026-04-29.md`
- `docs/architecture/README.md`

Applied changes:

- Added a repository-focused architecture matrix describing module repository counts,
  dominant persistence styles, high-risk write paths, and runtime integration roles.
- Categorized repositories into transactional aggregate-sync, header-only, and
  explicit-scope-bypass patterns.
- Indexed the matrix from the architecture knowledge base README.

Why this matters:

- Gives future repository work a single reference point for module ownership and risk.
- Makes the persistence landscape easier to navigate when extending or auditing ERP flows.
- Complements executable guardrails with a human-readable architecture map.

Why this matters:

- Warehouse and Inventory are the highest-read modules at runtime (every stock movement,
  pick, receive, and reservation touches stock_levels, warehouse_locations, and
  inventory_cost_layers). Missing indexes on hot query predicates cause full tenant-scoped
  scans that scale linearly with data volume per tenant.
- The guardrail test locks all six index names (two pre-existing, four new) so they
  cannot be silently dropped during schema refactors.

---

### 10.12 Finance cross-module event-source boundary guardrail

Files changed:

- `tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php`
  — added `test_finance_listeners_only_consume_events_from_approved_modules`

Applied changes:

- Added an explicit architecture invariant that inspects all Finance posting listener files
  and enforces that each listener imports exactly one domain event from an approved bounded
  context namespace:
  - `Modules\Purchase\Domain\Events\`
  - `Modules\Sales\Domain\Events\`
  - `Modules\Inventory\Domain\Events\`
  - `Modules\HR\Domain\Events\`
- The guardrail also asserts that each listener `handle()` signature is type-hinted with the
  same imported event class, preventing hidden drift between imports and runtime contracts.

Why this matters:

- Finance posting listeners are a critical integration seam. Without an explicit boundary
  contract, new couplings to unrelated modules can be introduced silently during feature work.
- This guardrail fails fast in CI if Finance starts consuming ad-hoc events from non-approved
  modules, preserving the intended cross-module architecture and reducing accidental coupling.

Focused validation:

- `./vendor/bin/phpunit tests/Unit/Architecture/CrossModuleFlowInvariantsTest.php --testdox`

Result: 10 passed, 0 failed.

---

### 10.13 Sales repository tenant-contract guardrails

Files changed:

- `tests/Unit/Architecture/SalesRepositoryTenantGuardrailsTest.php`

Applied changes:

- Added a new architecture guardrail test suite to lock tenant-first lookup
  contracts for all Sales aggregate repositories:
  - `SalesOrderRepositoryInterface::findByTenantAndSoNumber`
  - `SalesInvoiceRepositoryInterface::findByTenantAndInvoiceNumber`
  - `ShipmentRepositoryInterface::findByTenantAndShipmentNumber`
  - `SalesReturnRepositoryInterface::findByTenantAndReturnNumber`
- The guardrail asserts three invariants:
  1. Interface methods require `tenantId` as first `int` parameter.
  2. Eloquent implementations keep the same first-parameter contract.
  3. Implementation method bodies include an explicit `where('tenant_id', $tenantId)` filter.

Why this matters:

- Sales document numbers are business keys that can repeat across tenants in
  shared-database multi-tenant deployments.
- Locking the contract at interface + implementation + query-body levels prevents
  future refactors from accidentally dropping tenant scoping and creating
  cross-tenant data exposure risk.

Focused validation:

- `./vendor/bin/phpunit tests/Unit/Architecture/SalesRepositoryTenantGuardrailsTest.php --testdox`

Result: 9 passed, 0 failed.

---

### 10.14 SalesReturn aggregate line synchronization hardening

Files changed:

- `app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesReturnRepository.php`
- `tests/Unit/Architecture/SalesAggregateLineSyncGuardrailsTest.php`

Applied changes:

- Replaced SalesReturn line persistence from blanket delete/recreate to id-aware line synchronization.
- Updated existing lines in place when a line ID is present and tenant-scoped row matches.
- Inserted only unmatched lines.
- Pruned removed lines with tenant-scoped cleanup via `whereNotIn('id', $keptLineIds)`.
- Added a dedicated architecture guardrail that enforces the Sales aggregate repositories keep:
  1. no full `lines()->delete()` write pattern,
  2. id-aware kept-line tracking,
  3. tenant-scoped line update/cleanup filters,
  4. removal-only pruning behavior.

Why this matters:

- Blanket line replacement creates unnecessary row churn and can break historical linkage
  assumptions for downstream audit and traceability workflows.
- Aligning SalesReturn with SalesOrder/SalesInvoice/Shipment synchronization behavior reduces
  write amplification and lowers regression risk under retries/concurrent updates.
- The guardrail keeps this contract executable so future refactors cannot silently revert to
  destructive line replacement.

Focused validation:

- `./vendor/bin/phpunit tests/Unit/Architecture/SalesAggregateLineSyncGuardrailsTest.php --testdox`

Result: 1 passed, 0 failed.

---

### 10.15 Inventory repository tenant-contract guardrails + CostLayer/ValuationConfig update() security fix

Files changed:

- `app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php`
- `app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentValuationConfigRepository.php`
- `tests/Unit/Architecture/InventoryRepositoryTenantGuardrailsTest.php`

Applied changes:

- **Security fix**: `EloquentCostLayerRepository::update()` called `withoutGlobalScope('tenant')` without
  a compensating `where('tenant_id', ...)` guard, enabling cross-tenant cost-layer writes via a crafted
  entity id. Added `->where('tenant_id', $layer->getTenantId())` before `->where('id', ...)`.
- **Security fix**: `EloquentValuationConfigRepository::update()` had the same pattern; added
  `->where('tenant_id', $config->getTenantId())` before `->where('id', ...)`.
- **Guardrail test** (`InventoryRepositoryTenantGuardrailsTest`): 9 tests, 114 assertions covering:
  1. All six Inventory repository interfaces declare `int $tenantId` as the first parameter on every
     tenant-scoped lookup/mutation method (`findById`, `paginate`, `delete`, `markAsReceived`, etc.).
  2. Eloquent implementations for StockReservation, InventoryStock, TransferOrder, and CycleCount
     include `->where('tenant_id', $tenantId)` in their query methods.
  3. The two update-by-id methods that bypass the global tenant scope (`CostLayerRepository::update`
     and `ValuationConfigRepository::update`) now include the explicit tenant_id guard.

Why this matters:

- `withoutGlobalScope('tenant')` is necessary for shared-table queries with explicit joins (cost layers
  use qualified column names), but it must always be paired with a `where('tenant_id', ...)` guard to
  maintain tenant isolation semantics at the query level.
- The guardrail makes the bypass + guard pattern executable — future changes that remove the guard
  from either update method will immediately fail the suite, preventing silent regression.
- Aligning Inventory interfaces with the tenant-first parameter contract established for Sales and
  Purchase closes the final gap in cross-module repository contract consistency.

Focused validation:

- `./vendor/bin/phpunit tests/Unit/Architecture/InventoryRepositoryTenantGuardrailsTest.php --testdox`

Result: 9 passed, 0 failed.
