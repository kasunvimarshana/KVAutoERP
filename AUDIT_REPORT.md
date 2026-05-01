# KVAutoERP — Comprehensive Architecture & Design Audit Report

> **Scope**: Full end-to-end analysis of all 20 modules under `app/Modules/`, covering architecture, design patterns, data models, cross-module dependencies, integration flows, identified gaps, and recommendations.

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Module Inventory & Status Matrix](#2-module-inventory--status-matrix)
3. [Architecture Patterns](#3-architecture-patterns)
4. [Cross-Cutting Infrastructure](#4-cross-cutting-infrastructure)
5. [Data Model & Schema Analysis](#5-data-model--schema-analysis)
6. [Cross-Module Dependency Map](#6-cross-module-dependency-map)
7. [Integration & Event Flows](#7-integration--event-flows)
8. [Module Deep-Dive Findings](#8-module-deep-dive-findings)
9. [Architectural Gaps & Issues](#9-architectural-gaps--issues)
10. [Security Analysis](#10-security-analysis)
11. [Performance Considerations](#11-performance-considerations)
12. [Test Coverage Assessment](#12-test-coverage-assessment)
13. [Recommendations](#13-recommendations)

---

## 1. System Overview

KVAutoERP is a **multi-tenant SaaS ERP/CRM platform** built on:

| Dimension | Detail |
|---|---|
| Framework | Laravel 12, PHP 8.2+ |
| Architecture | Clean Architecture, modular monolith |
| Multi-tenancy | Header-driven (`X-Tenant-ID`), soft isolation |
| Auth | Laravel Passport (OAuth2) |
| Real-time | Laravel Reverb (WebSocket) |
| API docs | L5-Swagger / OpenAPI |
| Testing | PHPUnit, SQLite in-memory |
| Monetary precision | `DECIMAL(20,6)` throughout |
| Concurrency control | `row_version` (BIGINT) on all major tables |
| Module namespace | `Modules\<Module>\...` (not `App\Modules\...`) |

The platform currently has **20 modules** registered in `bootstrap/providers.php` in explicit dependency order.

---

## 2. Module Inventory & Status Matrix

### 2.1 Fully Implemented Modules (Domain + Application + Infrastructure)

| # | Module | Core Entities | Service Count (approx.) | Notable Features |
|---|---|---|---|---|
| 1 | **Audit** | AuditLog | 4 | HasAudit trait, withoutAudit() helper |
| 2 | **Auth** | AccessToken | ~15 | RBAC + ABAC strategies, Passport, SSO |
| 3 | **Configuration** | Country, Currency, Language, Timezone | 12 | Read-only reference data, no tenant scope |
| 4 | **Core** | (Base classes, traits) | BaseService, BaseRepo | HasUuid, HasTranslations, Broadcasting infra |
| 5 | **Customer** | Customer, CustomerAddress, CustomerContact | 12 | CustomerUserSynchronizer interface |
| 6 | **Employee** | Employee | 4 | EmployeeUserSynchronizer interface |
| 7 | **Finance** | Account, FiscalYear/Period, JournalEntry+Lines, Payment+Allocations, AR/AP Transactions, BankAccount/Transactions/Reconciliation, CreditMemo, CostCenter, NumberingSequence, ApprovalWorkflow, PaymentMethod/Terms | **~85** | Most complex module; event-driven JE creation; idempotency on payments |
| 8 | **HR** | AttendanceLog/Record, BiometricDevice, EmployeeDocument, LeaveBalance/Policy/Request/Type, PayrollItem/Run/Payslip/Line, PerformanceCycle/Review, Shift/Assignment | ~35 | Full HRM lifecycle; ProcessPayrollRun; PayrollRunApproved event |
| 9 | **Inventory** | StockMovement, StockReservation, StockLevel, CostLayer, CycleCount, TransferOrder, ValuationConfig, Batch, Serial, TraceLog | ~15 | FIFO/LIFO/FEFO/NearestBin/Manual allocation; UOM normalization; ReleaseExpiredReservations cron |
| 10 | **OrganizationUnit** | OrganizationUnit, OUType, OUAttachment, OUUser | 8 | Hierarchical org structure |
| 11 | **Pricing** | PriceList, PriceListItem, CustomerPriceList, SupplierPriceList | 8 | Price validity by date range |
| 12 | **Product** | Product, ProductVariant, Category, Brand, AttributeGroup/Attribute/Value, ComboItem, ProductIdentifier, UoM, UomConversion | ~20 | 5 product types; 5 valuation methods; UomConversionResolverService |
| 13 | **Purchase** | PurchaseOrder+Lines, GRN+Lines, PurchaseInvoice+Lines, PurchaseReturn+Lines | ~20 | Full procurement lifecycle; PurchaseInvoiceApproved event |
| 14 | **Sales** | SalesOrder+Lines, Shipment+Lines, SalesInvoice+Lines, SalesReturn+Lines | ~18 | Full order-to-cash lifecycle; SalesInvoicePosted / SalesPaymentRecorded events |
| 15 | **Supplier** | Supplier, SupplierAddress, SupplierContact, SupplierProduct | 12 | SupplierUserSynchronizer interface |
| 16 | **Tax** | TaxGroup, TaxRate, TaxRule, TransactionTax | 8 | Rule-based matching; compound tax support |
| 17 | **Tenant** | Tenant, TenantPlan, TenantAttachment, TenantSetting, TenantDomain | ~20 | TenantConfigManager; two service providers |
| 18 | **User** | User, Role, Permission, UserDevice, UserAttachment | ~15 | Role+Permission junction tables |
| 19 | **Warehouse** | Warehouse, WarehouseLocation | 8 | Location hierarchy |

### 2.2 Minimal Shell Modules

| Module | Status |
|---|---|
| **Shared** | Intentionally thin — provider + route surface only. No domain logic. |

### 2.3 Migration-Only Stubs

The following modules have migration schemas defined but **no Application or Domain code**:

`HR` (partial — has full code), others mentioned in CLAUDE.md as migration-only stubs. Review `bootstrap/providers.php` for exact current set.

---

## 3. Architecture Patterns

### 3.1 Layer Structure

Every fully implemented module follows the same three-layer Clean Architecture pattern:

```
Domain (pure PHP, no framework dependencies)
  └── Entities, RepositoryInterfaces, Events, Exceptions, ValueObjects
      ↕ (repository contracts only)
Application (orchestration layer)
  └── Contracts (service interfaces), Services (implementations), DTOs
      ↕ (implements repository interfaces, calls other service contracts)
Infrastructure (framework-aware)
  └── Eloquent Models + Repositories, HTTP Controllers + Requests + Resources,
      ServiceProvider, Middleware, Listeners, Broadcasting
```

**Dependency rule is upheld**: no Infrastructure imports found in Domain layer. Application layer depends only on Domain contracts.

### 3.2 Service Pattern

All services extend `Modules\Core\Application\Services\BaseService` and implement a `handle(array $data): mixed` template method. The `BaseService`:

- Wraps execution in a `DB::transaction()` for all write operations
- Collects and dispatches `$this->events` array after the transaction commits (via `Event::dispatch`)
- Provides `this->addEvent()` for queuing events within the transaction boundary

This is a **solid pattern** — events are guaranteed to fire only after successful commit.

### 3.3 Repository Pattern

- Interface in `Domain/RepositoryInterfaces/`
- Eloquent implementation in `Infrastructure/Persistence/Eloquent/Repositories/`
- `BaseRepository` provides a fluent builder pattern: `where()`, `whereIn()`, `with()`, `limit()`, `orderBy()`, `get()`, `paginate()`, `find()`, `save()`, `delete()`
- Repositories are tenant-scoped by filtering `tenant_id` explicitly

### 3.4 Controller Pattern

Controllers stay intentionally thin — they delegate entirely to injected service interfaces. No business logic observed in controllers. Form Request classes handle validation.

### 3.5 Provider-Driven Binding

Each module's `ServiceProvider` registers all interface→implementation bindings and event→listener mappings. The `LoadsModuleRoutesAndMigrations` Core trait handles route and migration registration.

---

## 4. Cross-Cutting Infrastructure

### 4.1 `HasUuid` Trait (`Core`)

Located at `Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid`.

- Boot hook: generates UUID on `creating` if primary key is empty
- Sets `getIncrementing() = false`, `getKeyType() = 'string'`
- **Gap**: UUID generation is at the Eloquent model layer (Infrastructure), not enforced in the Domain entity. Domain entities use `?int $id` — a UUID is only assigned when persisted.

### 4.2 `HasAudit` Trait (`Audit`)

Located at `Audit\Infrastructure\Persistence\Eloquent\Traits\HasAudit`.

- Hooks `saving`, `created`, `updated`, `deleted`, `restored` Eloquent events
- Captures `getOriginal()` diff and writes to `audit_logs`
- Provides `withoutAudit(callable $callback)` static helper to suppress audit within a closure
- Applied broadly across tenant-scoped models

### 4.3 `HasTenant` Trait (`Tenant`)

Located at `Tenant\Infrastructure\Persistence\Eloquent\Traits\HasTenant`.

- Used by Audit, Warehouse, Employee, OrganizationUnit, Sales (Shipment, ShipmentLine, SalesReturn, SalesReturnModel)
- **Inconsistency**: Not uniformly applied. Finance models, Inventory models, and others manage `tenant_id` via explicit fillable fields without this trait. Audit-confirmed: `AuditLogModel`, `WarehouseModel`, `WarehouseLocationModel`, `EmployeeModel`, `OrganizationUnitModel` use the trait; Finance, Inventory, Product, Customer, Supplier do not.

### 4.4 Multi-Tenancy Model

- `ResolveTenant` middleware reads `X-Tenant-ID` from request header
- Tenant context stored in `app('auth_context')` (config-driven)
- All queries must filter `tenant_id` manually in repositories — no global scope enforced
- **Gap**: No global Eloquent scope (e.g., `TenantScope`) to automatically filter queries. This is entirely developer-discipline dependent. A missed `tenant_id` filter in a repository would silently leak cross-tenant data.

### 4.5 Broadcasting Infrastructure (`Core`)

- `BroadcastService`, `ChannelManager`, `EventBroadcaster` in Core
- Channels: `TenantChannel`, `OrgUnitChannel`, `UserChannel` plus Presence variants
- Laravel Reverb for WebSocket transport

### 4.6 Optimistic Concurrency (`row_version`)

`row_version` (BIGINT) column exists on all major tables for optimistic concurrency control. However, no `row_version` increment logic was observed in any service. **Gap**: The column is defined in migrations but the application-layer increment/check logic is absent — `row_version` provides no protection at runtime unless implemented.

---

## 5. Data Model & Schema Analysis

### 5.1 Core Schema Conventions

| Convention | Applied |
|---|---|
| UUIDs as PKs | Via `HasUuid` on Eloquent models (mixed — not all models) |
| Integer PKs | `id` BIGINT unsigned auto-increment (standard for most tables) |
| `tenant_id` FK | All tenant-scoped tables (cascade delete or restrict) |
| `org_unit_id` FK | Tables that scope below tenant |
| `row_version` | All major transactional tables |
| `soft_deletes` | Broad usage (`deleted_at`) |
| `DECIMAL(20,6)` | All monetary and quantity values |
| Timestamps | All tables |

### 5.2 Notable Schema Designs

#### Tenants
- Dual plan tracking: `plan VARCHAR` (string field, e.g. `'free'`) **AND** `tenant_plan_id FK → tenant_plans`. Both exist simultaneously. **Redundancy risk**: they can diverge.
- `feature_flags`, `api_keys`, `settings`, `database_config`, `mail_config`, `cache_config`, `queue_config` all stored as JSON. Rich configuration but potentially difficult to index/query.

#### Users
- Unique constraint: `[tenant_id, org_unit_id, email]` — same email can exist in different org_units within the same tenant. **Potentially intentional** for matrix org structures but unusual for a CRM/ERP.

#### Journal Entries
- `nullableMorphs('reference')` → `reference_type`, `reference_id` — standard polymorphic FK
- Unique: `[tenant_id, reference_type, reference_id]` — prevents double-posting a source document
- Self-referential: `reversal_entry_id → journal_entries.id` for reversal tracking

#### Payments
- `party_type ENUM(customer, supplier)` + `party_id BIGINT` — **manual polymorphic** pattern (not using Laravel's `morphs()`)
- `idempotency_key` with unique constraint `[tenant_id, idempotency_key]` — good payment safety design
- `journal_entry_id` is nullable FK — journal entry is not created atomically with the payment; it's linked after creation via `PostPaymentService`

#### Stock Movements
- `nullableMorphs('reference')` — consistent with journal_entries
- `quantity_available` computed column: `storedAs('quantity_on_hand - quantity_reserved')` on `stock_levels`
- `total_cost` computed column: `storedAs('quantity * unit_cost')` on `stock_movements`
- 12 movement types covering full warehouse operations

#### Purchase Orders
- `grand_total` has **commented-out `storedAs`** — application computes it. Inconsistent with `stock_levels` / `stock_movements` which use `storedAs`.

### 5.3 Reference Data (Configuration Module)

Countries, Currencies, Languages, Timezones live in the Configuration module — **no tenant scope**. All tenants share the same reference tables. This is correct for global reference data.

### 5.4 Foreign Key Dependency Graph (abbreviated)

```
tenants
  └── users (tenant_id)
      └── employees (user_id, tenant_id)
  └── organization_units (tenant_id, parent_id self-FK)
      └── users (org_unit_id)
  └── customers (tenant_id)
      └── customer_addresses, customer_contacts
      └── ar_account_id → accounts
  └── suppliers (tenant_id)
      └── supplier_addresses, supplier_contacts, supplier_products
  └── products (tenant_id)
      └── product_variants, product_categories, product_brands
      └── unit_of_measures, uom_conversions
      └── income_account_id, cogs_account_id, inventory_account_id → accounts
  └── warehouses (tenant_id)
      └── warehouse_locations (warehouse_id)
  └── price_lists (tenant_id)
      └── price_list_items, customer_price_lists, supplier_price_lists
  └── tax_groups → tax_rates, tax_rules, transaction_taxes
  └── fiscal_years → fiscal_periods
      └── journal_entries (fiscal_period_id)
          └── journal_entry_lines (account_id, cost_center_id)
  └── accounts (tenant_id)
  └── payments (tenant_id)
      └── payment_allocations
  └── sales_orders (customer_id, warehouse_id, currency_id, price_list_id)
      └── sales_order_lines (product_id, variant_id, uom_id)
      └── shipments → shipment_lines
      └── sales_invoices → sales_invoice_lines
      └── sales_returns → sales_return_lines
  └── purchase_orders (supplier_id, warehouse_id, currency_id)
      └── purchase_order_lines
      └── grn_headers → grn_lines
      └── purchase_invoices → purchase_invoice_lines
      └── purchase_returns → purchase_return_lines
  └── stock_movements (product_id, location_id, batch_id, serial_id)
  └── stock_levels (product_id, location_id, batch_id, serial_id)
  └── stock_reservations, cost_layers, cycle_counts, transfer_orders
  └── payroll_runs → payslips → payslip_lines
  └── attendance_records, leave_requests, performance_reviews
```

---

## 6. Cross-Module Dependency Map

### 6.1 Direct Namespace Imports (Infrastructure layer only)

| Consumer Module | Imports From | Import Type |
|---|---|---|
| Customer (Model) | Finance (`AccountModel`) | Eloquent `BelongsTo` relation |
| Customer (Model) | Configuration (`CurrencyModel`) | Eloquent `BelongsTo` relation |
| Customer (Model) | OrganizationUnit (`OrganizationUnitModel`) | Eloquent `BelongsTo` relation |
| Customer (Model) | User (`UserModel`) | Eloquent `BelongsTo` relation |
| Finance (Listeners) | Sales, Purchase, HR, Inventory | Event class imports |
| Finance (Provider) | HR, Inventory, Purchase, Sales | Event → Listener registration |
| Inventory (Service) | Product (`UomConversionResolverService`) | Interface injection |
| Auth (Middleware) | All modules | Authorization checks |
| All modules | Core (traits, base classes) | Trait use / inheritance |
| All modules | Audit (HasAudit trait) | Trait use |
| All modules | Tenant (HasTenant trait, ResolveTenant middleware) | Trait + middleware |

### 6.2 Dependency Layers

```
Core ← (all modules)
Audit ← (all modules with tracked entities)
Configuration ← (Customer, Sales, Purchase, Pricing...)
Tenant ← (all tenant-scoped modules)
Auth ← (all protected route modules)

Finance ← Sales, Purchase, HR, Inventory (via events)
Inventory ← Product (UOM conversion), Warehouse (location validation)
Sales ← Customer, Product, Warehouse, Pricing, Tax, Finance
Purchase ← Supplier, Product, Warehouse, Pricing, Tax, Finance
HR ← Employee, User
```

### 6.3 No Circular Dependencies Detected

All cross-module dependencies flow in one direction. Finance depends on events from other modules (inbound) rather than calling their services directly, maintaining clean boundaries. The one exception is `CustomerModel` importing `AccountModel` from Finance at the Eloquent level — see §9.1.

---

## 7. Integration & Event Flows

### 7.1 Event-Driven Finance Integration

This is the most architecturally significant integration pattern in the system. Finance uses **synchronous event listeners** to create journal entries in response to business events from other modules.

**Events registered in `FinanceServiceProvider`:**

| Source Event | Finance Listener | Journal Entry Type |
|---|---|---|
| `Sales\SalesInvoicePosted` | `HandleSalesInvoicePosted` | DR Accounts Receivable / CR Revenue per line |
| `Sales\SalesPaymentRecorded` | `HandleSalesPaymentRecorded` | DR Bank/Cash / CR AR |
| `Sales\SalesReturnReceived` | `HandleSalesReturnReceived` | Reversal of revenue entries |
| `Purchase\PurchaseInvoiceApproved` | `HandlePurchaseInvoiceApproved` | DR Inventory/Expense / CR AP |
| `Purchase\PurchasePaymentRecorded` | `HandlePurchasePaymentRecorded` | DR AP / CR Bank |
| `Purchase\PurchaseReturnPosted` | `HandlePurchaseReturnPosted` | Reversal of payable entries |
| `HR\PayrollRunApproved` | `HandlePayrollRunApproved` | Payroll JE (DR Salary Expense / CR Payable) |
| `Inventory\StockAdjustmentRecorded` | `HandleStockAdjustmentRecorded` | Inventory adjustment JE |
| `Inventory\CycleCountCompleted` | `HandleCycleCountCompleted` | Cycle count variance JE |

**Key design details of listeners:**

1. **Idempotency / replay detection**: Each listener calls `journalAlreadyPosted(tenantId, reference_type, reference_id)` before creating a JE. This checks `journal_entries` for an existing entry with the same `reference_type`/`reference_id` — preventing duplicate JEs on event replay.

2. **Graceful degradation**: If required account IDs are null (e.g., `arAccountId === null`), the listener logs a warning and skips JE creation rather than throwing. This prevents hard failures but can silently produce unbalanced books.

3. **Synchronous execution**: No `ShouldQueue` interface implemented on any listener. All Finance journal entry creation runs **in-process, synchronously** within the originating request's transaction scope. This means a database error during JE creation will **not** roll back the originating Sales Invoice post (the events fire after the transaction commits per `BaseService` design).

4. **`HandleSalesInvoicePosted` validates line totals**: Aggregates credit amounts by `income_account_id`, checks `bccomp(creditTotal, grandTotal, 2)`. Skips with a warning if mismatch — again graceful degradation rather than hard failure.

### 7.2 Inventory Event Flow

```
RecordStockMovementService
  → creates StockMovement
  → fires StockAdjustmentRecorded event
    → Finance\HandleStockAdjustmentRecorded creates JE

CompleteCycleCountService
  → fires CycleCountCompleted event
    → Finance\HandleCycleCountCompleted creates JE

ReleaseExpiredStockReservationsCommand (cron)
  → fires ExpiredStockReservationsReleased event
```

### 7.3 Sales Order-to-Cash Flow

```
CreateSalesOrder
  → (no automatic reservation or tax calculation)
ConfirmSalesOrder
  → (status change)
CreateShipment / ProcessShipment
  → (no automatic Inventory StockMovement — GAP: see §9.3)
PostSalesInvoice
  → fires SalesInvoicePosted
    → Finance creates AR Journal Entry
RecordSalesPayment
  → fires SalesPaymentRecorded
    → Finance creates Cash Receipt Journal Entry
```

### 7.4 Payroll Flow

```
ProcessPayrollRun
  → creates Payslips (no tax calculation integration — manual)
  → fires PayslipGenerated per employee
ApprovePayrollRun
  → fires PayrollRunApproved
    → Finance\HandlePayrollRunApproved creates Payroll Journal Entry
```

---

## 8. Module Deep-Dive Findings

### 8.1 Finance Module

The most complex module (~85 services). Key findings:

- **Double-entry enforcement**: `CreateJournalEntryService` enforces `abs(debitTotal - creditTotal) > PHP_FLOAT_EPSILON` — correct float comparison per project conventions.
- **Fiscal period validation**: JE creation requires an open fiscal period. Prevents posting to closed periods.
- **PostPaymentService**: Marks a payment as `posted` but **does not create a Journal Entry**. The caller must separately call `CreateJournalEntry` and pass back the `journal_entry_id`. This two-step process requires orchestration that is not encapsulated anywhere.
- **FinancialReportService**: Uses raw DB queries directly (bypasses repository pattern). Acceptable for read-only reporting but worth noting.
- **`payments.party_id`**: Manual enum (`party_type: customer|supplier`) + raw integer `party_id` instead of Laravel morphs. Inconsistent with `nullableMorphs('reference')` used in `journal_entries` and `stock_movements`.
- **NumberingSequence**: Auto-numbering service for document numbers (invoices, orders, etc.) — good pattern.
- **ApprovalWorkflow**: Config-driven approval workflow with `ApprovalRequest` lifecycle (pending/approved/rejected/cancelled). Well-designed.
- **BankReconciliation**: Full bank rec workflow with `BankCategoryRule` for auto-categorization.
- **CreditMemo**: Separate lifecycle (issued → applied → voided).

### 8.2 Inventory Module

- **AllocationEngineService**: Resolves strategy from `ValuationConfig` (per tenant/product/warehouse/orgUnit/transactionType specificity), then delegates to strategy implementations (FIFO/LIFO/FEFO/NearestBin/Manual). Clean Strategy pattern.
- **RecordStockMovementService**: Normalizes quantity to base UOM using `UomConversionResolverService`, stores normalization in `metadata`, creates StockMovement, fires event. Uses `DB::transaction`. Well-implemented.
- **ReleaseExpiredStockReservationsCommand**: Scheduled Artisan command for releasing expired reservations. Events fired per batch.
- **`stock_levels.quantity_available`**: Database `storedAs` computed column — queries do not need to calculate this.
- **Serial/Batch tracking**: `is_serial_tracked`, `is_batch_tracked`, `is_lot_tracked` flags on Product. Batch/Serial entities exist.

### 8.3 HR Module

- **ProcessPayrollRun**: Loops through employees, applies active `PayrollItems` (earning/deduction, fixed or percentage). Creates `Payslip` entities. **Does not integrate with Tax module** for tax withholding calculations — payroll tax is not automated.
- **Payslip.journal_entry_id**: Nullable — payroll JE is created asynchronously via `ApprovePayrollRun` event.
- **LeaveBalance**: No auto-deduction observed in `CreateLeaveRequest` service — leave request approval logic unclear.
- **BiometricDevice**: `SyncBiometricDeviceService` and `ProcessAttendanceService` exist for attendance automation.

### 8.4 Auth Module

- **Dual strategy**: RBAC (`RbacAuthorizationStrategy`) checks permissions via `role_user` → `permission_role` and direct `permission_user` junction tables. ABAC (`AbacAuthorizationStrategy`) delegates to Laravel `Gate`.
- **`CheckPermission` middleware**: Calls `AuthorizationServiceInterface::hasPermission()` → returns 403 on failure.
- **Strategy selection**: `AuthorizationService` resolves which strategy to use (configurable, likely per tenant).
- **`TenantContextResolver`**: Resolves tenant from request for auth context.

### 8.5 Tax Module

- **Rule matching**: `findBestMatch(tenantId, productCategoryId, partyType, region)` — specificity-based matching.
- **Compound taxes**: Sorted non-compound before compound; compound rates apply to `baseAmount + accumulatedTax`.
- **`tax_account_id`** on `TaxRate`: Each rate maps to a liability account in Finance. Integration point requires accounts to be configured.
- **ResolveTaxService returns data, does not persist**: Caller responsible for persisting `TransactionTax` records via `RecordTransactionTaxesService`.
- **Gap**: `CreateSalesOrderService` does not call `ResolveTaxService` — tax totals must be passed in from outside (client or another orchestration layer).

### 8.6 Product Module

- **5 product types**: `physical`, `service`, `digital`, `combo`, `variable`
- **5 valuation methods**: `fifo`, `lifo`, `fefo`, `weighted_average`, `standard`
- **Account linkage**: Each product has `income_account_id`, `cogs_account_id`, `inventory_account_id`, `expense_account_id` FKs → Finance accounts. These are used by Finance listeners to build journal entry lines.
- **UomConversionResolverService**: Used by Inventory's `RecordStockMovementService` for UOM normalization. Clean cross-module service injection via interface.

### 8.7 Tenant Module

- **Two service providers**: `TenantServiceProvider` (CRUD, file uploads) + `TenantConfigServiceProvider` (runtime config management).
- **`TenantConfigManager`**: Manages per-tenant override of application config (mail, cache, queue, etc.).
- **`TenantConfigValueObjectFactory`**: Converts raw JSON config to typed value objects.
- **Redundant plan tracking**: `tenants.plan VARCHAR` + `tenant_plan_id FK → tenant_plans` (see §9.6).

### 8.8 Customer / Supplier Modules

- Both have `UserSynchronizerInterface` — allows linking a Customer/Supplier to a User account for portal access.
- `CustomerModel` imports `Finance\AccountModel` for the `ar_account_id` Eloquent relation — **direct Infrastructure-level cross-module coupling** (see §9.1).
- `customers.ar_account_id` and `suppliers.ap_account_id` FKs into Finance's `accounts` table.

---

## 9. Architectural Gaps & Issues

### 9.1 Eloquent-Level Cross-Module Coupling (Medium Severity)

**Issue**: `CustomerModel` imports `Finance\Infrastructure\Persistence\Eloquent\Models\AccountModel` directly for the `arAccount()` Eloquent relationship.

```php
use Modules\Finance\Infrastructure\Persistence\Eloquent\Models\AccountModel;
// ...
public function arAccount(): BelongsTo {
    return $this->belongsTo(AccountModel::class, 'ar_account_id');
}
```

This creates a hard compile-time dependency from Customer's Infrastructure layer into Finance's Infrastructure layer. If Finance is refactored or the Account model is moved, Customer breaks.

**Recommendation**: Either accept this as pragmatic coupling (it IS an FK at the DB level) or replace the eager-load relationship with a data contract / dedicated DTO that Finance exposes.

---

### 9.2 `PostPaymentService` Requires Manual JE Orchestration (Medium Severity)

**Issue**: `PostPaymentService` takes `journal_entry_id` as an optional input but does **not** create the journal entry. The caller must:
1. Call `CreateJournalEntryService` to create a JE
2. Call `PostPaymentService` with the resulting `journal_entry_id`

This is a two-step API with no atomic orchestrator. A payment can be posted without a journal entry (`journal_entry_id` is optional/nullable). This can result in posted payments with no accounting entry.

**Recommendation**: Create a `PostPaymentWithJournalEntryService` orchestrator that wraps both operations in a single transaction, or make `journal_entry_id` mandatory on the post operation.

---

### 9.3 No Automatic Inventory Movement on Shipment Processing (High Severity)

**Issue**: `ProcessShipmentService` in Sales processes a shipment (status change) but there is **no observed call** to `RecordStockMovementService` in Inventory. Similarly, creating a GRN in Purchase does not automatically trigger a stock receipt movement.

**Impact**: Shipments can be processed without reducing inventory. GRNs can be received without increasing stock. The modules are structurally decoupled but there is no event or direct call bridging this critical operational flow.

**Recommendation**: Either:
- Fire `ShipmentProcessed` / `GrnReceived` events from Sales/Purchase and add Inventory listeners
- Or have Sales/Purchase call Inventory service interfaces directly (acceptable cross-module coupling for tightly-coupled operational flows)

---

### 9.4 No Automatic Tax Calculation on Sales/Purchase Order Creation (Medium Severity)

**Issue**: `CreateSalesOrderService` creates a `SalesOrder` entity with `tax_total` passed directly from input data. It does not call `ResolveTaxService`. Tax totals must be pre-calculated client-side or by an orchestration layer not found in the codebase.

**Impact**: Tax calculations are not enforced server-side during order creation. Clients can submit arbitrary tax values.

**Recommendation**: Call `ResolveTaxService` within `CreateSalesOrderService` (or a dedicated `SalesOrderOrchestrationService`) to compute and validate tax totals server-side.

---

### 9.5 No Global Tenant Scope Enforcement (High Severity)

**Issue**: Multi-tenancy is enforced purely by developer discipline. Repositories must explicitly filter `tenant_id` in every query. There is no `TenantScope` Eloquent global scope that automatically applies `WHERE tenant_id = ?`.

**Impact**: A missing `tenant_id` filter in any repository method would silently return or modify cross-tenant data — a critical data isolation vulnerability in a multi-tenant SaaS.

**Recommendation**: Implement a `TenantScope` as a global Eloquent scope on all tenant-scoped models, **in addition to** explicit repository filters as a defense-in-depth measure.

---

### 9.6 Redundant Plan Tracking on Tenants (Low Severity)

**Issue**: `tenants` table has both `plan VARCHAR(50) DEFAULT 'free'` and `tenant_plan_id FK → tenant_plans`. These can diverge.

**Recommendation**: Remove `plan VARCHAR` and derive the plan name from the `tenant_plan_id` relationship, or vice versa.

---

### 9.7 `row_version` Column Without Application-Layer Enforcement (Medium Severity)

**Issue**: All major tables have `row_version BIGINT` for optimistic concurrency control, but no service or repository was observed to:
- Include `row_version` in UPDATE `WHERE` clauses
- Increment `row_version` on save
- Throw a `StaleEntityException` on version mismatch

**Impact**: The optimistic locking infrastructure exists in the schema but provides no runtime protection.

**Recommendation**: Implement version checks in `BaseRepository::save()` — check incoming `row_version` matches DB value before update, increment on save.

---

### 9.8 Finance Listeners Run Synchronously After Transaction Commit (Design Note)

**Issue**: Finance's event listeners (`HandleSalesInvoicePosted`, etc.) run synchronously in-process. The `BaseService` dispatches events **after** the originating transaction commits. If a listener fails (e.g., DB error creating the JE), the original Sales Invoice post has already committed.

**Impact**: The Sales/Purchase/HR operation succeeds but the corresponding Journal Entry is not created, leading to accounting gaps without automatic recovery.

**Recommendation**: Either:
- Queue listeners (implement `ShouldQueue`) for resilient async processing with retry
- Or wrap the full operation (source post + JE creation) in a single transaction at a higher orchestration layer (at the cost of tight coupling)

---

### 9.9 `users` Unique Index Allows Same Email Across Org Units (Design Observation)

**Issue**: `UNIQUE(tenant_id, org_unit_id, email)` means the same email address can exist as multiple user accounts within one tenant, provided they are in different org units.

**Assessment**: This may be intentional for scenarios where one person holds multiple roles (e.g., employee in Dept A and manager in Dept B). However, it complicates user lookup by email within a tenant and could create confusion.

**Recommendation**: Clarify the business intent. If a user can span org units, model this as `user_org_unit` assignments, with `UNIQUE(tenant_id, email)` on the user record itself.

---

### 9.10 `payments.party_id` vs Morph Inconsistency (Low Severity)

**Issue**: `journal_entries` and `stock_movements` use `nullableMorphs('reference')` (Laravel's polymorphic columns). `payments` uses a manual pattern: `party_type ENUM(customer,supplier)` + `party_id BIGINT` without database-level FK integrity.

**Recommendation**: Either standardize on Laravel morphs for polymorphic party references, or document the intentional distinction (e.g., the ENUM provides type safety at DB level that morphs don't).

---

### 9.11 Payroll Tax Integration Gap (Medium Severity)

**Issue**: `ProcessPayrollRunService` calculates gross/net salary using `PayrollItems` (earnings/deductions) but does not call `ResolveTaxService` for payroll tax (income tax / social contributions withholding).

**Impact**: Payslips do not include statutory payroll tax deductions. This is a functional gap for any jurisdiction with payroll tax obligations.

**Recommendation**: Integrate `ResolveTaxService` (or a dedicated `PayrollTaxService`) into `ProcessPayrollRunService` or the payslip generation step.

---

### 9.12 `HasUuid` Applied at Infrastructure Layer Only (Design Observation)

**Issue**: UUID generation via `HasUuid` is an Eloquent boot hook — it only fires when the model is saved via Eloquent. The Domain Entity has `?int $id` and only gets a UUID after persistence. This means domain entities don't have stable identifiers before save, complicating event sourcing or domain-level identity comparisons.

**Assessment**: Acceptable for the current CRUD-oriented architecture but worth noting if the system evolves toward event sourcing.

---

## 10. Security Analysis

### 10.1 Authentication

- Laravel Passport (OAuth2) for all API routes — industry standard
- `auth.configured` middleware (`AuthenticateWithConfiguredGuard`) on all protected routes
- Token-based, stateless — appropriate for API-first SaaS

### 10.2 Authorization

- RBAC via `role_user` → `permission_role` + direct `permission_user` junction tables
- ABAC via Laravel Gate delegation (supports policy classes)
- `CheckPermission` and `CheckRole` middleware on route groups
- Dual strategy is powerful but adds complexity — ensure strategy selection logic is well-tested

### 10.3 Multi-Tenant Isolation

- `X-Tenant-ID` header read by `ResolveTenant` middleware
- **Gap identified** (§9.5): No global Eloquent scope — relies on developer discipline
- No row-level security at DB layer — all isolation is application-level

### 10.4 Input Validation

- All write operations use dedicated `FormRequest` classes for validation
- Strong typing (`declare(strict_types=1)`) throughout prevents type coercion vulnerabilities
- DTO pattern (`fromArray()`) provides a clear boundary for input mapping

### 10.5 Monetary Precision

- `DECIMAL(20,6)` on all monetary values — no floating-point precision loss at storage
- `PHP_FLOAT_EPSILON` for float comparison where needed (per project conventions)
- `bcmath` used in Finance listeners (`bcadd`, `bccomp`) — correct for currency arithmetic

### 10.6 Idempotency

- Payments have `idempotency_key` with unique DB constraint — prevents duplicate payments
- Finance listeners have `journalAlreadyPosted()` replay detection — prevents duplicate JEs
- **Gap**: No idempotency protection observed on Sales Orders, Purchase Orders, or other creation operations

---

## 11. Performance Considerations

### 11.1 Indexes Observed

- Composite indexes on `stock_movements` (tenant + product + location + type, etc.)
- Composite indexes on `stock_levels` (unique: tenant + org_unit + product + variant + location + batch + serial)
- `idempotency_key` index on `payments`
- `reference_type + reference_id` indexes on `journal_entries`

### 11.2 Stored Computed Columns

- `stock_levels.quantity_available = quantity_on_hand - quantity_reserved` (storedAs)
- `stock_movements.total_cost = quantity * unit_cost` (storedAs)
- `purchase_orders.grand_total`: **Commented out** `storedAs` — computed at application level (inconsistency)

### 11.3 N+1 Query Risks

- `BaseRepository` has `$this->with` for eager loading, but usage depends on each repository implementation
- `ProcessPayrollRunService` loops over all `$employees` and all active `$activeItems` in-memory — O(n×m) with no DB-level join. For large payrolls this will be slow.

### 11.4 FinancialReportService

- Uses raw `DB::table()` queries with pagination — appropriate for reporting
- No caching layer observed — repeated GL queries will hit the DB every time

### 11.5 Synchronous Finance Listeners

- 9 cross-module Finance listeners run synchronously during request processing
- Each listener may execute `CreateJournalEntryService` which does a DB write + fiscal period lookup
- For high-volume scenarios (e.g., batch invoice posting), this adds latency to the source request

---

## 12. Test Coverage Assessment

### 12.1 Test File Inventory

Feature tests exist for all major modules:

| Test File | Coverage Target |
|---|---|
| `AuditEndpointsAuthenticatedTest` | Audit API authentication |
| `AuditRepositoryIntegrationTest` | Audit log writes |
| `AuditRoutesTest` | Route existence |
| `ConfigurationModuleMigrationSmokeTest` | Migration smoke |
| `CustomerEndpointsAuthenticatedTest` | Customer API |
| `CustomerNestedRepositoryIntegrationTest` | Address/Contact nested repos |
| `EmployeeEndpointsAuthenticatedTest` | Employee API |
| `EmployeeRepositoryIntegrationTest` | Employee persistence |
| `FinanceFiscalEndpointsAuthenticatedTest` | Fiscal Year/Period API |
| `FinanceListenerIntegrationTest` | All 9 Finance cross-module listeners |
| `FinancePaymentIdempotencyIntegrationTest` | Payment idempotency key |
| `FinanceRoutesTest` | Finance route existence |
| `HRAttendanceRecordIntegrationTest` | Attendance records |
| `HREndpointsAuthenticatedTest` | HR API |
| `HRRoutesTest` | HR routes |
| `InventoryAllocationStrategyIntegrationTest` | FIFO/LIFO/FEFO allocation |
| `InventoryCycleCountIntegrationTest` | Cycle count lifecycle |
| `InventoryCycleCountRoutesTest` | Cycle count routes |
| `InventoryReleaseExpiredReservationsCommandTest` | Cron command |
| `InventoryRoutesTest` | Inventory routes |
| `InventoryStockMovementIntegrationTest` | Stock movement creation |
| `InventoryStockReservationEndpointsAuthenticatedTest` | Reservation API |

### 12.2 Test Architecture

- `RefreshDatabase` used — SQLite in-memory, fast, no external DB dependency
- Integration tests cover the most critical business logic paths
- `FinanceListenerIntegrationTest` is notable — tests all 9 cross-module event→JE flows end-to-end

### 12.3 Observed Gaps in Test Coverage

- No unit tests visible for domain entities (value object invariants, entity state transitions)
- No tests for `ProcessPayrollRunService` payroll calculation logic
- No tests for `ResolveTaxService` compound tax calculation edge cases
- No tests for `AllocationEngineService` strategy resolution from `ValuationConfig`
- No tests for `TenantConfigManager` config override behavior
- No tests for authorization (RBAC/ABAC strategy) edge cases
- `Unit/` directory exists but appears empty

---

## 13. Recommendations

### Priority 1 — Critical (Address Before Production)

| # | Issue | Recommendation |
|---|---|---|
| P1-1 | No automatic inventory movement on shipment/GRN (§9.3) | Add `ShipmentProcessed` → Inventory listener or direct service call |
| P1-2 | No global tenant scope (§9.5) | Implement `TenantScope` global Eloquent scope as defense-in-depth |
| P1-3 | Finance listeners fire after transaction commit with no retry (§9.8) | Implement `ShouldQueue` on Finance listeners for resilient async processing |

### Priority 2 — High (Address in Next Sprint)

| # | Issue | Recommendation |
|---|---|---|
| P2-1 | `PostPaymentService` requires manual JE orchestration (§9.2) | Create atomic `PostPaymentWithJournalEntryService` |
| P2-2 | No server-side tax calculation on order creation (§9.4) | Integrate `ResolveTaxService` into order creation flow |
| P2-3 | `row_version` not enforced at application layer (§9.7) | Implement optimistic lock check in `BaseRepository::save()` |
| P2-4 | Payroll tax gap (§9.11) | Integrate tax calculation into `ProcessPayrollRunService` |

### Priority 3 — Medium (Address in Coming Weeks)

| # | Issue | Recommendation |
|---|---|---|
| P3-1 | Eloquent cross-module coupling in CustomerModel (§9.1) | Extract to DTO or accept and document as pragmatic coupling |
| P3-2 | Redundant `plan` + `tenant_plan_id` on Tenant (§9.6) | Remove `plan VARCHAR`, derive from FK |
| P3-3 | `payments.party_id` vs morph inconsistency (§9.10) | Standardize or document intentional distinction |
| P3-4 | N+1 risk in `ProcessPayrollRunService` (§11.3) | Optimize with chunked SQL or batch loading |
| P3-5 | `HasTenant` trait applied inconsistently (§4.3) | Audit all tenant-scoped models and apply consistently |

### Priority 4 — Low / Improvement (Backlog)

| # | Issue | Recommendation |
|---|---|---|
| P4-1 | `users` unique index includes `org_unit_id` (§9.9) | Clarify business intent; consider `UNIQUE(tenant_id, email)` |
| P4-2 | `HasUuid` only at Infrastructure layer (§9.12) | If event sourcing planned, generate UUIDs in Domain layer |
| P4-3 | Finance Listener graceful degradation silences accounting gaps (§7.1) | Add monitoring/alerting on skipped JE creation warnings |
| P4-4 | `purchase_orders.grand_total` not computed column (§5.2) | Re-enable `storedAs` or document why it's disabled |
| P4-5 | No unit tests for domain entities or value objects (§12.3) | Add PHPUnit unit tests for `PayrollRun`, `JournalEntry`, tax calculations |
| P4-6 | FinancialReportService bypasses repository pattern (§8.1) | Acceptable as-is; document exception |

---

## Appendix: Module Service Provider Registration Order

```php
// bootstrap/providers.php (in dependency order)
AppServiceProvider::class,
CoreServiceProvider::class,
ConfigurationServiceProvider::class,
SharedServiceProvider::class,
AuditServiceProvider::class,
AuthModuleServiceProvider::class,
TenantServiceProvider::class,
TenantConfigServiceProvider::class,
UserServiceProvider::class,
OrganizationUnitServiceProvider::class,
ProductServiceProvider::class,
PricingServiceProvider::class,
CustomerServiceProvider::class,
EmployeeServiceProvider::class,
SupplierServiceProvider::class,
TaxServiceProvider::class,
FinanceServiceProvider::class,
InventoryServiceProvider::class,
WarehouseServiceProvider::class,
PurchaseServiceProvider::class,
SalesServiceProvider::class,
HRServiceProvider::class,
```

---

*Audit performed: static analysis of all module source files, migrations, service implementations, event/listener bindings, and test coverage. No code was modified.*
