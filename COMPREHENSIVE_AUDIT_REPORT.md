# KVAutoERP — Comprehensive Audit Report

> Generated after full static analysis of 69 migration files (87+ tables), 8 fully-implemented PHP modules, and cross-reference against SKILL.md / AGENT.md specifications.

---

## Executive Summary

| Category | Critical | High | Medium | Low | Total |
|----------|----------|------|--------|-----|-------|
| Migration Structural | 2 | 1 | 0 | 0 | 3 |
| Decimal Precision | 1 | 0 | 0 | 0 | 1 (100+ columns) |
| One-Entity-Per-Migration | 0 | 1 | 0 | 0 | 1 (12 files) |
| Missing Spec Tables | 0 | 1 | 0 | 0 | 1 (8 tables) |
| Extra Non-Spec Tables | 0 | 0 | 1 | 0 | 1 (10 tables) |
| Missing tenant_id | 0 | 1 | 0 | 0 | 1 (15 tables) |
| Naming Conflicts | 0 | 1 | 0 | 0 | 1 |
| FK Constraints | 0 | 0 | 1 | 0 | 1 (6+ columns) |
| down() Bugs | 0 | 0 | 1 | 0 | 1 |
| Consistency Issues | 0 | 0 | 1 | 0 | 1 (8 items) |
| PHP Architecture | 0 | 1 | 2 | 2 | 5 |
| **Totals** | **3** | **6** | **6** | **2** | **17 findings** |

**Test Baseline**: 156 tests, 791 assertions — all passing (1 warning, 52 PHPUnit notices).

---

## 1. CRITICAL — Migration Structural Failures

### 1.1 CRIT-001: Broken `cycle_counts` Migration (Will Not Execute)

**File**: `app/Modules/Inventory/database/migrations/2024_01_01_900010_create_cycle_counts_tables.php`

**Problem**: File is missing the required Laravel migration class wrapper. Contains raw `Schema::create()` calls without:
- `declare(strict_types=1);`
- `use Illuminate\...` statements
- `return new class extends Migration { ... }` wrapper
- `down()` method

**Impact**: Migration will silently fail or throw a fatal error on `php artisan migrate`. No cycle count tables will be created.

**Fix**: Wrap in proper anonymous migration class matching the project pattern.

---

### 1.2 CRIT-002: Product Migration FK Ordering Bug

**File**: `app/Modules/Product/database/migrations/2024_01_01_600005_create_products_table.php`

**Problem**: Line `$table->foreignId('brand_id')->nullable()->constrained('product_brands')` references the `product_brands` table, but `product_brands` is created later in `2024_01_01_600009_create_product_brands_table.php`.

**Impact**: `php artisan migrate` will fail with "Base table or view not found" on a fresh database.

**Fix**: Either:
- Rename the brands migration to `600004` so it runs before products, OR
- Remove the `constrained()` call and defer the FK to the deferred FK migration (`999999`)

---

### 1.3 CRIT-003: Systemic Decimal Precision Violations (~100+ Columns)

**Problem**: The spec mandates:
- Monetary/quantity: `DECIMAL(20,6)`
- Exchange rates: `DECIMAL(20,10)`
- Percentages: `DECIMAL(10,6)`

**Actual**: Nearly every migration uses undersized types:
- Monetary: `decimal(15,4)` — **~55 columns** across Purchase, Sales, Finance, Tenant, Customer, Supplier, Product, Inventory, Warehouse, Pricing
- Quantity: `decimal(15,4)` — **~15 columns** across Inventory, Product, Pricing
- Exchange rate: `decimal(15,6)` — **~10 columns** (spec requires 20,10)
- Percentage: `decimal(5,2)` — **~4 columns** (spec requires 10,6)

**Exception**: `cycle_count_lines` correctly uses `decimal(20,6)` — the only compliant file.

**Affected Modules (all migration-stub modules)**:

| Module | Affected Columns |
|--------|-----------------|
| Finance | subtotal, tax_total, discount_total, grand_total, debit, credit, amount, current_balance, running_balance, opening_balance, closing_balance, exchange_rate (all files) |
| Purchase | unit_price, line_total, discount_pct, tax_amount, ordered_qty, received_qty, invoiced_qty, restocking_fee, exchange_rate (all files) |
| Sales | unit_price, line_total, discount_pct, tax_amount, ordered_qty, shipped_qty, invoiced_qty, unit_cost, line_cost, restocking_fee, exchange_rate (all files) |
| Tenant | tenant_plans.price |
| Customer | customers.credit_limit |
| Supplier | supplier_products.last_purchase_price |
| Product | products.standard_cost, products.last_purchase_price |
| Pricing | price_list_items.price, price_list_items.min_qty |
| Inventory | stock_levels (all qty), stock_movements.quantity, batches.initial_qty/remaining_qty, inventory_cost_layers (all), stock_transfers/adjustments (all qty/cost) |
| Warehouse | (no monetary columns, but warehouse_locations has no precision issues) |

**Impact**: Data truncation and rounding errors in production. Financial calculations will lose precision. Multi-currency operations with small exchange rate differentials will fail.

**Fix**: Change all `decimal(15,4)` → `decimal(20,6)`, all `decimal(15,6)` exchange rates → `decimal(20,10)`, all `decimal(5,2)` percentages → `decimal(10,6)`.

---

## 2. HIGH — Significant Design Issues

### 2.1 HIGH-001: 12 Multi-Table Migration Violations

**Rule**: One entity (table) per migration file.

| File | Tables Created | Module |
|------|---------------|--------|
| `100001_create_shared_tables.php` | countries, currencies, languages, timezones (4) | Shared |
| `300005_create_user_roles_permissions_tables.php` | roles, permissions, role_user, permission_role, permission_user (5) | User |
| `600003_create_product_attribute_tables.php` | attribute_groups, attributes, attribute_values (3) | Product |
| `600007_create_product_variant_tables.php` | product_variants, variant_attribute_values (2) | Product |
| `700001_create_tax_tables.php` | tax_classes, tax_rates, tax_rules (3) | Tax |
| `900005_create_stock_transfer_tables.php` | stock_transfers, stock_transfer_lines (2) | Inventory |
| `900007_create_stock_adjustment_tables.php` | stock_adjustments, stock_adjustment_lines (2) | Inventory |
| `900010_create_cycle_counts_tables.php` | cycle_count_headers, cycle_count_lines (2) | Inventory |
| `110001_create_fiscal_tables.php` | fiscal_years, fiscal_periods (2) | Finance |
| `110003_create_journal_entries_table.php` | journal_entries, journal_entry_lines (2) | Finance |
| `110005_create_payments_table.php` | payment_methods, payments, payment_allocations (3) | Finance |
| `110006_create_bank_accounts_table.php` | bank_accounts, bank_category_rules, bank_transactions, bank_reconciliations (4) | Finance |
| Plus 6 Purchase/Sales files | Each creates header + lines (2 each) | Purchase/Sales |

**Fix**: Split each into one migration per table, maintaining correct ordering.

---

### 2.2 HIGH-002: Missing `tenant_id` on 15 Tenant-Scoped Tables

These tables belong to tenant-scoped modules but lack the `tenant_id` column:

| Table | Module | Expected Behavior |
|-------|--------|-------------------|
| user_devices | User | Device belongs to user who belongs to tenant |
| uom_conversions | Product | UoM conversions are tenant-specific |
| product_variants | Product | Variants are tenant-specific |
| variant_attribute_values | Product | Variant attrs are tenant-specific |
| combo_items | Product | Combo items are tenant-specific |
| attribute_values | Product | Attribute values are tenant-specific |
| price_list_items | Pricing | Price list items are tenant-specific |
| customer_price_lists | Pricing | Junction is tenant-specific |
| supplier_price_lists | Pricing | Junction is tenant-specific |
| stock_transfer_lines | Inventory | Transfer lines are tenant-specific |
| stock_adjustment_lines | Inventory | Adjustment lines are tenant-specific |
| cycle_count_lines | Inventory | Count lines are tenant-specific |
| journal_entry_lines | Finance | JE lines are tenant-specific |
| payment_allocations | Finance | Allocations are tenant-specific |
| bank_transactions | Finance | Bank txns are tenant-specific |

**Note**: Some of these (lines tables) could argue tenant_id is derivable from the parent. However, for direct query performance and the middleware-based filtering pattern, tenant_id on every row is the safest approach.

---

### 2.3 HIGH-003: Missing Spec-Defined Tables (8 Tables)

| Missing Table | Spec Location | Purpose |
|---------------|--------------|---------|
| `tenant_domains` | SKILL.md §8.2 | Custom domains per tenant |
| `org_unit_users` | SKILL.md §8.3 | User ↔ OrgUnit junction table |
| `payment_terms` | AGENT.md §4.3 | Configurable payment terms (net 30, etc.) |
| `variant_attributes` | SKILL.md §8.8 | Attribute metadata for variant generation (distinct from `variant_attribute_values`) |
| `transfer_orders` + `transfer_order_lines` | AGENT.md §4.4 | Planned warehouse transfers (actual has `stock_transfers` instead — naming mismatch) |
| `numbering_sequences` | AGENT.md §4.5 | Auto-number generators (SO-00001, PO-00001, etc.) |
| `approval_workflow_configs` + `approval_requests` | AGENT.md §4.6 | Configurable approval chains |

**Note on `stock_transfers` vs `transfer_orders`**: The actual code uses `stock_transfers`/`stock_transfer_lines`; the spec uses `transfer_orders`/`transfer_order_lines`. These may be intentional synonyms, but the naming should be unified.

**Note on `payment_terms`**: Actual code uses `payment_terms_days` (integer column) on customers/suppliers instead of a FK to a `payment_terms` table. This is simpler but less flexible than the spec.

---

### 2.4 HIGH-004: `tax_classes` vs `tax_groups` Naming Conflict

**Spec** (SKILL.md §8.14): Defines `tax_groups` table, and products spec says `tax_class_id (FK → tax_groups)`.

**Actual**: Migration creates `tax_classes` table and all FK columns reference `tax_class_id → tax_classes`.

**Affected FK columns** (5+ tables): `products.tax_class_id`, `purchase_order_lines.tax_class_id`, `sales_order_lines.tax_class_id`, `purchase_invoice_lines.tax_class_id`, `sales_invoice_lines.tax_class_id`

**Fix**: Rename table to `tax_groups` and FK columns to `tax_group_id` per spec, OR update spec. Must be consistent.

---

### 2.5 HIGH-005: Circular Module Dependencies (PHP Architecture)

| Dependency | Direction | Files |
|-----------|-----------|-------|
| Core ↔ Auth | Core imports Auth service contract; Auth extends Core base controller | Bidirectional |
| Auth ↔ User | Auth imports User contracts/models; User imports Auth use case | Bidirectional |

**Impact**: Violates Clean Architecture's dependency rule. Core should have zero outward dependencies.

**Fix**: Extract shared contracts to Core or a Shared module. Auth should depend on User (not vice versa). Core should not depend on Auth.

---

### 2.6 HIGH-006: Deferred FK Naming Convention Violation

**File**: `database/migrations/2024_01_01_999999_add_remaining_foreign_keys.php`

**Problem**: Uses auto-generated Laravel constraint names (`{table}_{column}_foreign`) instead of the project convention `{table}_{column}_fk`.

**Fix**: Add explicit `->name('{table}_{column}_fk')` to each FK constraint.

---

## 3. MEDIUM — Correctness & Consistency Issues

### 3.1 MED-001: Extra Non-Spec Tables (10 Tables)

Tables that exist in migrations but are not defined in SKILL.md or AGENT.md:

| Table | Module | Notes |
|-------|--------|-------|
| `tenant_attachments` | Tenant | Polymorphic attachment (not in spec but useful) |
| `org_unit_attachments` | OrganizationUnit | Polymorphic attachment |
| `user_attachments` | User | Polymorphic attachment |
| `product_brands` | Product | Separate brands table (spec puts brand_id on product but no brands table) |
| `ar_transactions` | Finance | AR subledger (spec doesn't define separate AR/AP tables) |
| `ap_transactions` | Finance | AP subledger |
| `payment_methods` | Finance | Payment method lookup (spec uses enum on payments instead) |
| `transaction_taxes` | Finance | Tax line items (spec doesn't define this) |
| `stock_adjustments` + `stock_adjustment_lines` | Inventory | Spec uses stock_movements for adjustments |

**Recommendation**: Keep attachment tables and product_brands (useful extensions). Reconcile AR/AP/payment_methods with spec — either update spec or remove tables.

---

### 3.2 MED-002: FK Columns Without Any Constraint

These columns are defined as `unsignedBigInteger` with no FK constraint, and they are NOT included in the deferred FK migration:

| Table.Column | Expected FK Target |
|-------------|-------------------|
| `org_units.default_revenue_account_id` | accounts |
| `org_units.default_expense_account_id` | accounts |
| `org_units.default_receivable_account_id` | accounts |
| `org_units.default_payable_account_id` | accounts |
| `org_units.default_inventory_account_id` | accounts |
| `org_units.warehouse_id` | warehouses |
| `org_units.manager_user_id` | users |
| `audit_logs.tenant_id` | tenants |
| `audit_logs.user_id` | users |
| `ar_transactions.customer_id` | customers |
| `ap_transactions.supplier_id` | suppliers |
| `user_attachments.tenant_id` | tenants |
| `org_unit_attachments.tenant_id` | tenants |

**Fix**: Add these to the deferred FK migration.

---

### 3.3 MED-003: `down()` Drop-Order Bug in Bank Migration

**File**: `app/Modules/Finance/database/migrations/2024_01_01_110006_create_bank_accounts_table.php`

**Problem**: `down()` drops `bank_category_rules` before `bank_transactions`, but `bank_transactions.category_rule_id` has a FK to `bank_category_rules`.

**Fix**: Reorder `down()` to drop `bank_transactions` first, then `bank_category_rules`.

---

### 3.4 MED-004: Inconsistencies Across Modules

| Issue | Details |
|-------|---------|
| Computed columns | PO lines use `storedAs` for `line_total`; SO lines don't; PI lines have it commented out |
| Missing exchange_rate | `shipments` has `currency_id` but no `exchange_rate` column |
| Polymorphic typing | `credit_memos.party_type` is `string`; `payments.party_type` is `enum` — should be consistent |
| SoftDeletes mismatch | `journal_entries` has softDeletes; `journal_entry_lines` doesn't |
| Financial doc SoftDeletes | No Purchase or Sales document tables use softDeletes (financial documents shouldn't be hard-deleted) |
| Auth migration overlap | `300001_create_auth_tables.php` redundantly adds `password`, `remember_token` to users table — already in `300001_create_users_table.php` |
| Missing timestamps | `stock_movements`, `trace_logs`, `audit_logs` lack standard `timestamps()` — use custom datetime columns only |
| Missing line_number | Some line tables have `line_number (int)` per spec; need to verify all line tables include it |

---

### 3.5 MED-005: Missing `declare(strict_types=1)` in PHP Files

| File | Type |
|------|------|
| `app/Modules/Core/config/core.php` | Config |
| `app/Modules/Core/Shared/Helpers/helpers.php` | Helper |
| `app/Modules/Auth/routes/api.php` | Routes |
| `app/Modules/Tenant/config/tenant.php` | Config |
| `app/Modules/Tenant/routes/api.php` | Routes |

---

### 3.6 MED-006: Structure Drift from Convention

**Core Module**:
- Repository interfaces in `Domain/Contracts/Repositories/` instead of `Domain/RepositoryInterfaces/`
- Repository implementations in `Infrastructure/Persistence/Repositories/` instead of `Infrastructure/Persistence/Eloquent/Repositories/`

**Auth Module**:
- Custom persistence file placement, missing standard layer folder names

---

## 4. LOW — Minor Issues

### 4.1 LOW-001: Unused Traits/Classes

| Item | Location | Status |
|------|----------|--------|
| `BaseModel` | Core module | Defined but never extended; all models extend `Model` directly |
| `HasUuid` trait | Core module | Defined but unused; all models use integer auto-increment PKs |
| `HasTenant` trait | Core module | Defined but unused; tenant isolation done via middleware + repository filtering |

**Recommendation**: Either integrate these into the codebase or remove as dead code.

---

### 4.2 LOW-002: Transaction Wrapping Inconsistency

| Service | Wrapped in DB::transaction? |
|---------|---------------------------|
| `RegisterUserService` (Auth) | Yes |
| `ResetPasswordService` (Auth) | No |
| `RefreshTokenService` (Auth) | No |

**Recommendation**: Wrap all write operations in transactions consistently.

---

## 5. Normalization Assessment (1NF–BCNF)

### 5.1 1NF Compliance
**Status: PASS with caveats**
- All tables have atomic columns (no repeating groups)
- `metadata(JSON)` columns exist on many tables — acceptable for flexible/unstructured data but should not contain data that needs querying (move to columns if frequently filtered)

### 5.2 2NF Compliance
**Status: PASS**
- All non-key attributes depend on the full primary key
- Line tables correctly use their own `id` PK with FK to parent

### 5.3 3NF Compliance
**Status: PASS with one exception**
- `storedAs` computed columns (`line_total = unit_price * quantity`) are technically transitive dependencies but are acceptable as materialized computed columns for query performance
- Exception: `products.last_purchase_price` is derivable from purchase history — denormalized for performance (acceptable)

### 5.4 BCNF Compliance
**Status: PASS**
- No non-trivial functional dependencies where a non-candidate key determines part of a candidate key

---

## 6. Spec vs Actual — Table Reconciliation Matrix

### Tables in Spec but Missing from Migrations

| Spec Table | Spec Source | Status |
|-----------|------------|--------|
| `tenant_domains` | SKILL.md §8.2 | **Missing** — no migration exists |
| `org_unit_users` | SKILL.md §8.3 | **Missing** — no junction table |
| `payment_terms` | AGENT.md §4.3 | **Replaced** by `payment_terms_days` integer column |
| `variant_attributes` | SKILL.md §8.8 | **Missing** — only `variant_attribute_values` exists |
| `transfer_orders` | AGENT.md §4.4 | **Renamed** to `stock_transfers` |
| `transfer_order_lines` | AGENT.md §4.4 | **Renamed** to `stock_transfer_lines` |
| `numbering_sequences` | AGENT.md §4.5 | **Missing** — no migration exists |
| `approval_workflow_configs` | AGENT.md §4.6 | **Missing** — no migration exists |
| `approval_requests` | AGENT.md §4.6 | **Missing** — no migration exists |
| `stock_reservations` | SKILL.md §8.11 | **Need verification** |

### Tables in Migrations but Not in Spec

| Actual Table | Module | Status |
|-------------|--------|--------|
| `tenant_attachments` | Tenant | **Extra** — useful extension |
| `org_unit_attachments` | OrganizationUnit | **Extra** — useful extension |
| `user_attachments` | User | **Extra** — useful extension |
| `product_brands` | Product | **Extra** — spec has brand_id on products but no brands table |
| `ar_transactions` | Finance | **Extra** — spec uses journal entries for AR |
| `ap_transactions` | Finance | **Extra** — spec uses journal entries for AP |
| `payment_methods` | Finance | **Extra** — spec uses enum on payments.method |
| `transaction_taxes` | Finance | **Extra** — spec doesn't define separate tax line items |
| `stock_adjustments` | Inventory | **Extra** — spec handles via stock_movements |
| `stock_adjustment_lines` | Inventory | **Extra** — companion to stock_adjustments |

---

## 7. Prioritized Remediation Plan

### Phase 1: Critical Fixes (Must Fix Before Any Migration Run)

| # | Issue | Action | Est. Files |
|---|-------|--------|-----------|
| 1 | CRIT-001 | Fix broken `cycle_counts` migration — wrap in proper class | 1 |
| 2 | CRIT-002 | Rename `600009` brands migration to `600004` | 1 (rename) |
| 3 | CRIT-003 | Fix all decimal precisions across all migrations | ~25 files |

### Phase 2: High Priority (Required for Spec Compliance)

| # | Issue | Action | Est. Files |
|---|-------|--------|-----------|
| 4 | HIGH-001 | Split multi-table migrations into one-per-table | ~18 files → ~40 files |
| 5 | HIGH-002 | Add `tenant_id` + FK to 15 tables | ~12 files |
| 6 | HIGH-003 | Create missing spec tables (migrations) | ~6 new files |
| 7 | HIGH-004 | Rename `tax_classes` → `tax_groups` and update all FKs | ~6 files |
| 8 | HIGH-005 | Resolve circular dependencies (Core↔Auth, Auth↔User) | ~4 files |
| 9 | HIGH-006 | Fix FK naming convention in deferred migration | 1 file |

### Phase 3: Medium Priority (Correctness & Consistency)

| # | Issue | Action | Est. Files |
|---|-------|--------|-----------|
| 10 | MED-002 | Add orphaned FK columns to deferred migration | 1 file |
| 11 | MED-003 | Fix `down()` drop order in bank migration | 1 file |
| 12 | MED-004 | Fix computed column, exchange_rate, party_type, softDeletes inconsistencies | ~8 files |
| 13 | MED-005 | Add `declare(strict_types=1)` to 5 PHP files | 5 files |
| 14 | MED-006 | Reorganize Core/Auth folder structure to match convention | ~6 files |

### Phase 4: Low Priority (Cleanup)

| # | Issue | Action | Est. Files |
|---|-------|--------|-----------|
| 15 | LOW-001 | Either integrate or remove unused traits/classes | 3 files |
| 16 | LOW-002 | Wrap all write services in DB::transaction | 2 files |

---

## 8. Test Impact Assessment

Current test suite: **156 tests, 791 assertions — all passing**.

| Remediation Phase | Test Impact |
|------------------|-------------|
| Phase 1 (Decimals, structural fixes) | Migration tests may need schema assertion updates |
| Phase 2 (Split migrations, add tables) | Migration order tests need updating; new table tests needed |
| Phase 3 (Consistency) | Minimal — mostly migration file changes |
| Phase 4 (Cleanup) | None expected |

**Recommendation**: Run full test suite after each phase to verify no regressions.

---

## Appendix A: Complete Migration File Inventory

### Module Migration Counts

| Module | Files | Tables |
|--------|-------|--------|
| Shared | 1 | 4 |
| Tenant | 4 | 5 |
| User | 4 | 8 |
| Auth | 2 | 3 |
| OrganizationUnit | 3 | 3 |
| Customer | 3 | 3 |
| Supplier | 4 | 4 |
| Product | 9 | 13 |
| Tax | 1 | 3 |
| Pricing | 3 | 4 |
| Warehouse | 2 | 2 |
| Inventory | 8 | 14 |
| Purchase | 4 | 8 |
| Sales | 5 | 10 |
| Finance | 6 | 14 |
| Audit | 1 | 1 |
| Framework | 3 | 3 (cache, jobs, failed_jobs) |
| Deferred FKs | 1 | — |
| **Total** | **64+** | **87+** |

### Migration Boot Order (per AGENT.md — 14 Phases)

| Phase | Sequence Range | Module |
|-------|---------------|--------|
| 1 | 100001 | Shared |
| 2 | 200001–200004 | Tenant |
| 3 | 300001–300005 | User |
| 4 | 300010–300011 | Auth |
| 5 | 350001–350003 | OrganizationUnit |
| 6 | 400001–400003 | Customer |
| 7 | 500001–500004 | Supplier |
| 8 | 600001–600011 | Product |
| 9 | 700001 | Tax |
| 10 | 750001–750003 | Pricing |
| 11 | 800001–800002 | Warehouse |
| 12 | 900001–900010 | Inventory |
| 13 | 1000001–1000004 | Purchase |
| 14 | 1100001–1100005 | Sales |
| 15 | 110001–110006 | Finance |
| 16 | 130001 | Audit |
| 17 | 999999 | Deferred FKs |

---

## Appendix B: Decimal Precision Fix Reference

| Current | Required | Use |
|---------|----------|-----|
| `decimal(15,4)` | `decimal(20,6)` | All monetary values and quantities |
| `decimal(15,6)` | `decimal(20,10)` | All exchange rates |
| `decimal(5,2)` | `decimal(10,6)` | All percentages (discount_pct, tax rates) |

---

*End of Audit Report*
