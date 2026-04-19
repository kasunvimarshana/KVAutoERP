# AGENT.md — Enterprise SaaS Multi-Tenant ERP/CRM Platform
**Document Type:** Autonomous Agent Operational Guide  
**Companion To:** SKILL.md v2.0  
**Version:** 1.1 (Updated to reflect actual implementation state)  
**Last Reviewed:** 2026-04-19

> **Implementation Note:** This guide covers both implemented modules and planned specifications.
> 8 modules are fully implemented (Core, Auth, Tenant, User, OrganizationUnit, Product, Finance, Audit).
> 9 modules have migration schemas only (Customer, Employee, Supplier, Pricing, Tax, Warehouse, Inventory, Purchase, Sales).
> Decision trees and event catalogs for migration-only modules describe the target design.

---

## ADDITIONAL AUDIT CORRECTIONS (Round 2)

*Issues identified during cross-review of SKILL.md and the original knowledge base:*

| # | Issue | Fix |
|---|---|---|
| 1 | `tax_class_id` used in products/order lines vs `tax_group_id` in tax module | **Unified to `tax_group_id`** throughout all tables |
| 2 | `transfer_orders` table missing — referenced in movement types but never defined | Added: Section 4.4 |
| 3 | `numbering_sequences` table missing — cited in tenant settings, never declared | Added: Section 4.5 |
| 4 | `approval_workflows` / `approval_requests` missing — cited as configurable, never declared | Added: Section 4.6 |
| 5 | No cross-module domain event catalog anywhere in the knowledge base | Added: Section 7 |
| 6 | No migration boot/dependency order defined | Added: Section 8 |
| 7 | No Laravel module service provider registration pattern | Added: Section 9 |
| 8 | No agent behavioral workflow or decision trees | Added: Sections 2–3 |
| 9 | No testing strategy or standards | Added: Section 10 |
| 10 | No security hardening checklist | Added: Section 11 |
| 11 | No API versioning strategy | Added: Section 12 |
| 12 | No queue/job definitions | Added: Section 13 |
| 13 | No Reverb (WebSocket) channel definitions | Added: Section 14 |
| 14 | No code generation pre-flight/post-flight checklists | Added: Section 15 |

---

## TABLE OF CONTENTS

1. [Agent Identity, Role & Behavioral Contract](#1-agent-identity-role--behavioral-contract)
2. [Agent Behavioral Workflow](#2-agent-behavioral-workflow)
3. [Decision Trees](#3-decision-trees)
4. [Missing Table Definitions (Round 2 Fixes)](#4-missing-table-definitions-round-2-fixes)
   - 4.1 Tax Naming Unification
   - 4.2 Transfer Orders
   - 4.3 Numbering Sequences
   - 4.4 Approval Workflows
   - 4.5 Complete Reference Tables
5. [Complete Module Table Index](#5-complete-module-table-index)
6. [Cross-Module Dependency Map](#6-cross-module-dependency-map)
7. [Domain Event Catalog](#7-domain-event-catalog)
8. [Migration Boot Order](#8-migration-boot-order)
9. [Laravel Module Service Provider Pattern](#9-laravel-module-service-provider-pattern)
10. [Testing Strategy & Standards](#10-testing-strategy--standards)
11. [Security Hardening Checklist](#11-security-hardening-checklist)
12. [API Design & Versioning Strategy](#12-api-design--versioning-strategy)
13. [Queue Jobs Catalog](#13-queue-jobs-catalog)
14. [Real-Time (Reverb) Channel Definitions](#14-real-time-reverb-channel-definitions)
15. [Code Generation Checklists](#15-code-generation-checklists)
16. [Error Handling Standards](#16-error-handling-standards)
17. [Implementation Sequence](#17-implementation-sequence)
18. [Self-Correction Protocol](#18-self-correction-protocol)

---

## 1. AGENT IDENTITY, ROLE & BEHAVIORAL CONTRACT

### Identity
You are an **autonomous Full-Stack Engineer and Principal Systems Architect** operating on an enterprise SaaS multi-tenant ERP/CRM platform built with Laravel.

### Behavioral Contract
These are non-negotiable behaviors that apply to **every single interaction**:

| Behavior | Rule |
|---|---|
| **Read before writing** | Always fully read existing files, migrations, and context before generating anything |
| **Audit first** | Before coding, identify all architectural flaws, inconsistencies, and debt |
| **Fix everything** | Never defer a known issue; fix it immediately and document the fix |
| **Maintain context** | Track all previously generated code, schemas, and decisions across the session |
| **Strict typing** | All PHP files begin with `declare(strict_types=1)` |
| **No hallucination** | If a table, field, or class is referenced, it must have been formally defined |
| **No shortcuts** | Do not generate placeholder, stub, or TODO code in production files |
| **Validate assumptions** | If a field name or relationship is ambiguous, resolve it explicitly before proceeding |
| **Single source of truth** | Resolve all naming/design conflicts by choosing the most precise, industry-standard option |

### Primary Reference Documents
- `SKILL.md` — Canonical system specification (architecture, modules, schemas, business rules)
- `AGENT.md` (this file) — Operational workflow, decision trees, event catalog, checklists
- All existing code in `app/Modules/` — Must be read before any additions or refactors

---

## 2. AGENT BEHAVIORAL WORKFLOW

This is the mandatory thought process for **every task**:

```
┌─────────────────────────────────────────────────────────┐
│  STEP 1 — INTAKE                                        │
│  Read the full task request. Identify:                  │
│  • Which module(s) are affected                         │
│  • Which tables/entities are involved                   │
│  • Which events will be triggered                       │
│  • Which layers need changes (Domain/App/Infra/Pres)    │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│  STEP 2 — AUDIT EXISTING STATE                          │
│  Read all relevant existing files:                      │
│  • Existing migrations for the module                   │
│  • Existing models, services, repositories              │
│  • Existing events and listeners                        │
│  • Related modules' contracts in Shared/               │
│  Identify: gaps, conflicts, violations, debt            │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│  STEP 3 — PLAN                                          │
│  Before writing any code, produce a structured plan:    │
│  • List all files to be created / modified              │
│  • List all migrations needed (in dependency order)     │
│  • List all events to be fired                          │
│  • List all cross-module impacts                        │
│  • Confirm plan aligns with SKILL.md and AGENT.md       │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│  STEP 4 — IMPLEMENT                                     │
│  Follow the layer order:                                │
│  1. Migration(s) — database schema first                │
│  2. Domain — interfaces, models, value objects, events  │
│  3. Application — DTOs, commands, services              │
│  4. Infrastructure — repositories, adapters, listeners  │
│  5. Presentation — controllers, requests, resources     │
│  6. Routes — register in module routes file             │
│  7. Service Provider — bind interfaces                  │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│  STEP 5 — VALIDATE                                      │
│  Run the post-generation checklist (Section 15.2):      │
│  • Are all FKs defined with explicit constraints?       │
│  • Are all monetary fields DECIMAL(20,6)?               │
│  • Does every financial event produce a journal entry?  │
│  • Is tenant scope applied to all models?               │
│  • Are all mutations written to audit_logs?             │
│  • Do all cross-module calls use Shared/ contracts?     │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│  STEP 6 — DOCUMENT & REPORT                             │
│  • Summarize what was created / changed / fixed         │
│  • List any remaining issues found but deferred         │
│  • Confirm no regressions introduced                    │
└─────────────────────────────────────────────────────────┘
```

---

## 3. DECISION TREES

### 3.1 — When to Use Events vs Direct Service Calls

```
Is the effect in the SAME module?
    ├── YES → Direct service call is acceptable
    └── NO  → MUST use a domain event
               └── Is the consuming module critical (Finance, Audit)?
                       ├── YES → Use synchronous listener OR queued with high priority
                       └── NO  → Use queued listener (async)
```

### 3.2 — When Does a Business Action Require a Journal Entry?

```
Does the action change any of the following?
    • Inventory value (stock in/out/adjust/transfer)
    • Accounts Receivable balance
    • Accounts Payable balance
    • Cash / Bank balance
    • Revenue or COGS
    ├── YES → A journal entry MUST be created and posted
    │          └── Is the fiscal period open?
    │                  ├── YES → Post immediately
    │                  └── NO  → Throw PeriodClosedException; reject the action
    └── NO  → No journal entry required
```

### 3.3 — Choosing Inventory Valuation on Outbound

```
What is the product's configured valuation_method?
    ├── fifo         → Select oldest cost layer with remaining quantity
    ├── lifo         → Select newest cost layer with remaining quantity
    ├── fefo         → Select layer with nearest expiry_date (batch required)
    ├── weighted_avg → Calculate: SUM(qty * cost) / SUM(qty) across all layers
    ├── specific     → Use unit_cost from the specific serial/batch record
    └── tenant_default → Recurse with tenant's configured default method
```

### 3.4 — How to Resolve a Price for an Order Line

```
Given: customer_id, product_id, variant_id, quantity, currency, order_date
    1. Find all price_lists assigned to this customer (customer_price_lists)
       filtered by: type=sales, currency match, valid on order_date
    2. From those lists, find price_list_items matching:
       product_id (+ variant_id if set) + uom + min_qty ≤ quantity ≤ max_qty
    3. Sort results by priority DESC
    4. Return first match — if none found:
       → Check promotional price lists (type=sales, no customer restriction)
       → Check tiered price lists
       → Check default price list
       → Fall back to product.standard_sales_price
       → Throw NoPriceFoundException if still nothing
```

### 3.5 — Return Disposition Decision

```
On return line receipt:
    What is the condition?
        ├── good      → Auto-disposition: restock (requires QC pass if enabled)
        ├── damaged   → Auto-disposition: quarantine; manual decision to restock or scrap
        ├── expired   → Auto-disposition: scrap (cannot restock expired goods)
        └── defective → Auto-disposition: quarantine; decision: vendor_return or scrap

    After disposition:
        ├── restock       → stock_movement(return_in) + cost layer re-insert
        ├── scrap         → stock_movement(write_off) + journal: Dr Loss / Cr Inventory
        ├── quarantine    → move to quarantine warehouse_location; no stock movement yet
        └── vendor_return → stock_movement(return_out) + generate debit_note
```

### 3.6 — Soft Delete vs Hard Delete

```
Is the entity master data?
    (products, customers, suppliers, employees, accounts, warehouses, users, org_units)
    ├── YES → SOFT DELETE ONLY (set deleted_at); never hard delete
    └── NO
        Is the entity a transactional record?
        (orders, invoices, payments, journal_entries, stock_movements)
        ├── YES → NEVER DELETE; void/cancel via status field + reversal entry
        └── NO
            Is the entity a config/reference record?
            (price_lists, tax_rates, settings)
            └── Soft delete preferred; hard delete only if no FK references exist
```

---

## 4. MISSING TABLE DEFINITIONS (ROUND 2 FIXES)

---

### 4.1 Tax Naming Unification — `tax_class_id` → `tax_group_id`

**Issue:** The original knowledge base used `tax_class_id` in `products` and order line tables but the tax module defines `tax_groups`. This is an inconsistency that would cause runtime FK failures.

**Fix:** Use `tax_group_id` (FK → `tax_groups.id`) everywhere. Apply to:
- `products.tax_group_id`
- `purchase_order_lines.tax_group_id`
- `purchase_invoice_lines.tax_group_id`
- `sales_order_lines.tax_group_id`
- `sales_invoice_lines.tax_group_id`

---

### 4.2 Transfer Orders

**Issue:** `stock_movements.movement_type = 'transfer'` exists, and warehouse-to-warehouse transfers are a core ERP function, but no header/line table was ever defined.

```sql
transfer_orders:       id, tenant_id, org_unit_id (nullable),
                       transfer_number (unique per tenant),
                       fiscal_period_id,
                       status (draft|approved|in_transit|received|cancelled),
                       from_warehouse_id (FK → warehouses),
                       to_warehouse_id (FK → warehouses),
                       requested_by_user_id,
                       approved_by_user_id (nullable),
                       transfer_date, expected_receipt_date,
                       notes, metadata(JSON),
                       timestamps, deleted_at

transfer_order_lines:  id, transfer_order_id, line_number (int),
                       product_id, variant_id (nullable),
                       batch_id (nullable), serial_id (nullable),
                       from_location_id (FK → warehouse_locations),
                       to_location_id (FK → warehouse_locations),
                       uom_id,
                       requested_qty DECIMAL(20,6),
                       shipped_qty DECIMAL(20,6) DEFAULT 0,
                       received_qty DECIMAL(20,6) DEFAULT 0,
                       unit_cost DECIMAL(20,6),
                       notes

-- Stock movement on transfer ship:
--   type=transfer, from_location_id=source, to_location_id=transit/destination
-- Stock movement on transfer receipt:
--   type=receipt (at destination), linked to transfer_order_line via reference_type/id
```

**Transfer States:**
```
draft → approved → in_transit (shipped from source) → received (confirmed at destination)
     └→ cancelled (before in_transit)
```

---

### 4.3 Numbering Sequences

**Issue:** The system requires configurable document numbering (PO-2025-0001, INV-00023, etc.) but no table was ever defined despite being referenced in tenant settings.

```sql
numbering_sequences:   id, tenant_id,
                       document_type (purchase_order|sales_order|grn|shipment|
                                      transfer_order|purchase_invoice|sales_invoice|
                                      purchase_return|sales_return|payment|
                                      journal_entry|credit_memo),
                       prefix VARCHAR(20),      -- e.g. "PO-"
                       suffix VARCHAR(20),      -- e.g. "" or "-DRAFT"
                       separator VARCHAR(5),    -- e.g. "-"
                       padding_length INT,      -- e.g. 5 → "00001"
                       include_year BOOL,       -- if true → "PO-2025-00001"
                       include_month BOOL,      -- if true → "PO-202501-00001"
                       current_value BIGINT,    -- auto-incremented on each use
                       reset_on_year BOOL,      -- reset current_value each fiscal year
                       is_active BOOL,
                       UNIQUE (tenant_id, document_type)
```

**Usage (Application Service):**
```php
// In Shared/Services/NumberingService.php
public function next(string $tenantId, string $documentType): string {
    // DB::transaction + SELECT FOR UPDATE on the sequence row
    // Increment current_value, build formatted string, return
}
// Format: {prefix}{year?}{month?}{separator}{padded_value}{suffix}
// Example: PO-2025-00042
```

---

### 4.4 Approval Workflows

**Issue:** "Approval workflows per document type" are cited as configurable per tenant but no schema was ever defined.

```sql
approval_workflow_configs: id, tenant_id,
                            document_type (purchase_order|sales_order|purchase_return|
                                           sales_return|transfer_order|journal_entry|
                                           purchase_invoice|payment),
                            is_enabled BOOL,
                            min_amount_trigger DECIMAL(20,6) (nullable),  -- require approval above this amount
                            steps(JSON),  -- ordered list of approver role_ids or user_ids
                            created_at, updated_at

approval_requests:         id, tenant_id,
                           document_type, document_id,  -- polymorphic
                           step_number INT,
                           status (pending|approved|rejected|withdrawn),
                           requested_by_user_id,
                           assigned_to_role_id (nullable),
                           assigned_to_user_id (nullable),
                           decided_by_user_id (nullable),
                           decision_at (nullable),
                           comments TEXT,
                           created_at, updated_at
```

**Approval Flow:**
```
Document created (draft)
  └→ Is approval enabled for this document type AND amount?
        ├── NO  → Document proceeds directly to confirmed/approved
        └── YES → Create approval_request(s) per configured steps
                    └→ Approver acts (approve/reject)
                          ├── approved (all steps) → Document status → approved
                          └── rejected (any step)  → Document status → rejected
                                                      fire ApprovalRejectedEvent
```

---

### 4.5 Complete Reference Tables (Formally Defined)

These global/tenant-agnostic tables support the entire system and must be seeded before any tenant data.

```sql
-- ── Global Reference (no tenant_id) ────────────────────────────
currencies:     id, code CHAR(3) UNIQUE (ISO 4217),
                name, symbol, symbol_native,
                decimal_places TINYINT DEFAULT 2,
                is_active BOOL DEFAULT TRUE

countries:      id, code CHAR(2) UNIQUE (ISO 3166-1 alpha-2),
                code3 CHAR(3), name, official_name,
                phone_code VARCHAR(10),
                currency_code CHAR(3) (FK → currencies.code),
                is_active BOOL DEFAULT TRUE

timezones:      id, name (IANA, e.g. "Asia/Colombo"),
                utc_offset VARCHAR(10), abbr VARCHAR(10), is_active BOOL

languages:      id, code CHAR(5) (IETF, e.g. "en-US"),
                name, native_name, is_rtl BOOL, is_active BOOL

-- ── Tenant-Scoped Reference ──────────────────────────────────────
payment_terms:  id, tenant_id,
                name (e.g. "Net 30"), net_days INT,
                discount_days INT DEFAULT 0,
                discount_pct DECIMAL(10,6) DEFAULT 0,
                description, is_active BOOL, timestamps
```

---

## 5. COMPLETE MODULE TABLE INDEX

*Every table in the system, organized by module. Use this as a quick lookup to avoid re-defining or duplicating tables.*

### Core Module
`currencies` · `countries` · `timezones` · `languages` · `attachments`

### Tenant Module
`tenants` · `tenant_plans` · `tenant_domains` · `tenant_settings`

### OrganizationUnit Module
`org_unit_types` · `org_units` · `org_unit_users`

### User Module
`users` · `roles` · `permissions` · `role_user` · `permission_role` · `permission_user` · `user_devices`
*(+ Laravel Passport tables: `oauth_clients`, `oauth_access_tokens`, `oauth_refresh_tokens`, `oauth_personal_access_clients`)*

### Customer Module
`customers` · `customer_addresses` · `customer_contacts`

### Employee Module
`employees` · `employee_addresses` · `employee_contacts`

### Supplier Module
`suppliers` · `supplier_addresses` · `supplier_contacts` · `supplier_products`

### Product Module
`product_categories` · `attribute_groups` · `attributes` · `attribute_values` · `products` · `product_variants` · `variant_attributes` · `combo_items` · `units_of_measure` · `uom_conversions` · `product_identifiers`

### Pricing Module
`price_lists` · `price_list_items` · `customer_price_lists` · `supplier_price_lists`

### Warehouse Module
`warehouses` · `warehouse_locations`

### Inventory Module
`stock_levels` · `batches` · `serials` · `stock_movements` · `stock_reservations` · `inventory_cost_layers` · `cycle_count_headers` · `cycle_count_lines` · `trace_logs`

### Purchase Module
`purchase_orders` · `purchase_order_lines` · `grn_headers` · `grn_lines` · `purchase_invoices` · `purchase_invoice_lines` · `purchase_returns` · `purchase_return_lines`

### Sales Module
`sales_orders` · `sales_order_lines` · `shipments` · `shipment_lines` · `sales_invoices` · `sales_invoice_lines` · `sales_returns` · `sales_return_lines` · `credit_memos`

### Transfer Module *(clarified — part of Inventory or Warehouse)*
`transfer_orders` · `transfer_order_lines`

### Finance Module
`fiscal_years` · `fiscal_periods` · `accounts` · `journal_entries` · `journal_entry_lines` · `tax_groups` · `tax_rates` · `tax_rules` · `payments` · `payment_allocations` · `bank_accounts` · `bank_transactions` · `bank_category_rules` · `bank_reconciliations`

### Shared / Cross-Cutting
`payment_terms` · `numbering_sequences` · `approval_workflow_configs` · `approval_requests` · `audit_logs`

---

## 6. CROSS-MODULE DEPENDENCY MAP

This map defines which modules a given module **reads from** (data dependency) and **listens to** (event dependency).

```
┌─────────────┐     depends on
│    Core     │◄──── (bootstraps everything; no dependencies)
└─────────────┘
       ▲
┌─────────────┐     depends on Core
│   Tenant    │
└─────────────┘
       ▲
┌─────────────┐     depends on Core, Tenant
│   OrgUnit   │
└─────────────┘
       ▲
┌─────────────┐     depends on Core, Tenant, OrgUnit
│    User     │
└─────────────┘
       ▲
┌──────────────────────────────────────────┐
│  Customer / Employee / Supplier          │
│  depend on: Core, Tenant, User, Finance  │
│  (for AR/AP account auto-creation)       │
└──────────────────────────────────────────┘
       ▲
┌─────────────┐     depends on Core, Tenant
│   Product   │     reads Finance.accounts (for account mapping)
└─────────────┘
       ▲
┌─────────────┐     depends on Product
│   Pricing   │     reads Customer, Supplier
└─────────────┘
       ▲
┌─────────────┐     depends on Core, Tenant, OrgUnit
│  Warehouse  │
└─────────────┘
       ▲
┌─────────────┐     depends on Product, Warehouse, Finance
│  Inventory  │     fires → Finance (cost layer changes)
└─────────────┘
       ▲
┌─────────────┐     depends on Supplier, Product, Inventory,
│  Purchase   │     Pricing, Warehouse, Finance
└─────────────┘     fires → Inventory, Finance, Audit
       ▲
┌─────────────┐     depends on Customer, Product, Inventory,
│    Sales    │     Pricing, Warehouse, Finance
└─────────────┘     fires → Inventory, Finance, Audit
       ▲
┌─────────────┐     depends on all modules
│   Finance   │     listens to events from Purchase, Sales, Inventory
└─────────────┘
```

**Golden Rule:** No module imports another module's concrete classes. All dependencies are expressed through events or Shared/ contracts.

---

## 7. DOMAIN EVENT CATALOG

Every domain event, its **publisher module**, its **listener modules**, and its **expected side effects**.

### Purchase Module Events

| Event | Publisher | Listeners | Side Effects |
|---|---|---|---|
| `PurchaseOrderConfirmed` | Purchase | Inventory | Create stock reservations (optional) |
| `GoodsReceiptPosted` | Purchase | Inventory, Finance, Audit | Create stock_movements(receipt), update stock_levels, post journal entry: Dr Inventory / Cr AP |
| `PurchaseInvoiceApproved` | Purchase | Finance, Audit | Post journal entry if not already posted at GRN |
| `PurchaseInvoicePaid` | Purchase | Finance, Audit | Post payment journal: Dr AP / Cr Bank |
| `PurchaseReturnPosted` | Purchase | Inventory, Finance, Audit | Create stock_movements(return_out), adjust cost layers, post journal: Dr AP / Cr Inventory |

### Sales Module Events

| Event | Publisher | Listeners | Side Effects |
|---|---|---|---|
| `SalesOrderConfirmed` | Sales | Inventory | Create stock_reservations per order line |
| `SalesOrderCancelled` | Sales | Inventory | Release stock_reservations |
| `ShipmentPosted` | Sales | Inventory, Finance, Audit | Create stock_movements(shipment), update stock_levels, post journal: Dr AR + Dr COGS / Cr Revenue + Cr Inventory |
| `SalesInvoiceSent` | Sales | Finance, Notification | Update AR aging; notify customer |
| `SalesInvoicePaid` | Sales | Finance, Audit | Post payment journal: Dr Bank / Cr AR |
| `SalesReturnPosted` | Sales | Inventory, Finance, Audit | Create stock_movements(return_in or write_off), post journal: Dr Revenue / Cr AR; create credit_memo |

### Inventory Module Events

| Event | Publisher | Listeners | Side Effects |
|---|---|---|---|
| `StockAdjustmentApproved` | Inventory | Finance, Audit | Post journal: Dr/Cr Inventory Adjustment / Cr/Dr Inventory |
| `TransferOrderShipped` | Inventory | Inventory, Audit | Create stock_movements(transfer) at source |
| `TransferOrderReceived` | Inventory | Inventory, Audit | Create stock_movements(receipt) at destination |
| `BatchStatusChanged` | Inventory | Audit, Notification | Log status change; notify if recalled or expired |
| `CycleCountApproved` | Inventory | Finance, Audit | Post adjustment journal for all variances |
| `StockMovementRecorded` | Inventory | Audit, AIDC | Write trace_log entry |

### Finance Module Events

| Event | Publisher | Listeners | Side Effects |
|---|---|---|---|
| `JournalEntryPosted` | Finance | Audit | Write audit_log entry |
| `FiscalPeriodClosed` | Finance | All modules | Block further postings to that period |
| `FiscalYearClosed` | Finance | Finance | Auto-reverse accruals; transfer net income to retained earnings |
| `BankTransactionImported` | Finance | Finance | Attempt category rule matching; create draft journal if matched |
| `BankReconciliationCompleted` | Finance | Audit | Log completed reconciliation |

### Tenant / User Module Events

| Event | Publisher | Listeners | Side Effects |
|---|---|---|---|
| `TenantCreated` | Tenant | Finance | Seed default chart of accounts for the new tenant |
| `TenantSuspended` | Tenant | User | Revoke all active OAuth tokens for tenant users |
| `UserCreated` | User | Audit | Log user creation |
| `CustomerCreated` | Customer | Finance | Auto-create AR control account linkage |
| `SupplierCreated` | Supplier | Finance | Auto-create AP control account linkage |
| `ApprovalRequestDecided` | Shared | Purchase/Sales/Finance | Update parent document status |

---

## 8. MIGRATION BOOT ORDER

Migrations must be run in dependency order. Tables with FK references must be created **after** the tables they reference.

```
PHASE 0 — Global Reference (no tenant_id, no FKs to other app tables)
  01. currencies
  02. countries
  03. timezones
  04. languages

PHASE 1 — Tenant Foundation
  05. tenant_plans
  06. tenants
  07. tenant_domains
  08. tenant_settings

PHASE 2 — User & Auth Foundation
  09. payment_terms
  10. users
  11. roles
  12. permissions
  13. role_user
  14. permission_role
  15. permission_user
  16. user_devices
  17. (laravel/passport tables — run via passport:install)

PHASE 3 — Organization
  18. org_unit_types
  19. org_units
  20. org_unit_users

PHASE 4 — Finance Foundation (must exist before parties; AR/AP account linkage)
  21. fiscal_years
  22. fiscal_periods
  23. accounts
  24. tax_groups
  25. tax_rates
  26. tax_rules

PHASE 5 — Parties
  27. customers
  28. customer_addresses
  29. customer_contacts
  30. employees
  31. employee_addresses
  32. employee_contacts
  33. suppliers
  34. supplier_addresses
  35. supplier_contacts

PHASE 6 — Product
  36. product_categories
  37. attribute_groups
  38. attributes
  39. attribute_values
  40. units_of_measure
  41. uom_conversions
  42. products
  43. product_variants
  44. variant_attributes
  45. combo_items
  46. product_identifiers
  47. supplier_products

PHASE 7 — Pricing
  48. price_lists
  49. price_list_items
  50. customer_price_lists
  51. supplier_price_lists

PHASE 8 — Warehouse
  52. warehouses
  53. warehouse_locations

PHASE 9 — Inventory
  54. batches
  55. serials
  56. stock_levels
  57. inventory_cost_layers
  58. stock_movements
  59. stock_reservations
  60. cycle_count_headers
  61. cycle_count_lines
  62. trace_logs

PHASE 10 — Transfer
  63. transfer_orders
  64. transfer_order_lines

PHASE 11 — Purchase
  65. purchase_orders
  66. purchase_order_lines
  67. grn_headers
  68. grn_lines
  69. purchase_invoices
  70. purchase_invoice_lines
  71. purchase_returns
  72. purchase_return_lines

PHASE 12 — Sales
  73. sales_orders
  74. sales_order_lines
  75. shipments
  76. shipment_lines
  77. sales_invoices
  78. sales_invoice_lines
  79. sales_returns
  80. sales_return_lines
  81. credit_memos

PHASE 13 — Finance (transactional)
  82. journal_entries
  83. journal_entry_lines
  84. payments
  85. payment_allocations
  86. bank_accounts
  87. bank_transactions
  88. bank_category_rules
  89. bank_reconciliations

PHASE 14 — Cross-Cutting
  90. attachments
  91. audit_logs
  92. numbering_sequences
  93. approval_workflow_configs
  94. approval_requests
```

---

## 9. LARAVEL MODULE SERVICE PROVIDER PATTERN

Each module registers its own service provider. The root `AppServiceProvider` boots all modules.

### Module Service Provider Template

```php
<?php
// app/Modules/<Module>/Infrastructure/Providers/<Module>ServiceProvider.php
declare(strict_types=1);

namespace Modules\<Module>\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\<Module>\Domain\RepositoryInterfaces\<Entity>RepositoryInterface;
use Modules\<Module>\Infrastructure\Persistence\Eloquent\Repositories\Eloquent<Entity>Repository;
use Modules\<Module>\Application\Contracts\<Entity>ServiceInterface;
use Modules\<Module>\Application\Services\<Entity>Service;

class <Module>ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interfaces → Eloquent Implementations
        $this->app->bind(
            <Entity>RepositoryInterface::class,
            Eloquent<Entity>Repository::class
        );

        // Bind Service Interfaces → Application Services
        $this->app->bind(
            <Entity>ServiceInterface::class,
            <Entity>Service::class
        );
    }

    public function boot(): void
    {
        // Load module routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        // Load module migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
```

### Root Registration (bootstrap/providers.php)

```php
// Currently registered providers (12):
return [
    App\Providers\AppServiceProvider::class,
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    Modules\Configuration\Infrastructure\Providers\ConfigurationServiceProvider::class,
    Modules\Shared\Infrastructure\Providers\SharedServiceProvider::class,
    Modules\Audit\Infrastructure\Providers\AuditServiceProvider::class,
    Modules\Auth\Infrastructure\Providers\AuthModuleServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantConfigServiceProvider::class,
    Modules\User\Infrastructure\Providers\UserServiceProvider::class,
    Modules\OrganizationUnit\Infrastructure\Providers\OrganizationUnitServiceProvider::class,
    Modules\Product\Infrastructure\Providers\ProductServiceProvider::class,
    Modules\Finance\Infrastructure\Providers\FinanceServiceProvider::class,
];
```

### Tenant Isolation — Current Implementation

Tenant isolation is currently implemented via **middleware**, not via a global Eloquent scope:

```php
// Modules\Tenant\Infrastructure\Http\Middleware\ResolveTenant.php
// Reads X-Tenant-ID from request headers and resolves the tenant
// Applied to routes via 'resolve.tenant' middleware alias
```

Repositories filter `tenant_id` explicitly in queries. The `HasTenant` trait exists in Core but is **not currently used** by any model.

> **Note:** `HasUuid` trait also exists in Core but is unused. All models use integer auto-increment primary keys.

### Base Model (Unused)

The `BaseModel` abstract class exists in Core with `SoftDeletes` but is **not extended by any model**. All 26 Eloquent models extend `Illuminate\Database\Eloquent\Model` directly (or `Authenticatable` for `UserModel`).

---

## 10. TESTING STRATEGY & STANDARDS

### Test Types

| Type | Location | Scope | Tool |
|---|---|---|---|
| Unit | `tests/Unit/Modules/<Module>/` | Domain services, value objects, calculators | PHPUnit |
| Feature | `tests/Feature/Modules/<Module>/` | Full HTTP request → DB flow | PHPUnit + Laravel TestCase |
| Integration | `tests/Integration/` | Multi-module event flows | PHPUnit |
| Database | `tests/Database/` | Migration integrity, FK constraints | PHPUnit |

### Unit Test Standards

```php
// Every Application Service must be unit-tested via mock repository
class PurchaseOrderServiceTest extends TestCase
{
    public function test_confirms_purchase_order_and_fires_event(): void
    {
        $repo = Mockery::mock(PurchaseOrderRepositoryInterface::class);
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $repo->shouldReceive('findById')->once()->andReturn($mockPO);
        $repo->shouldReceive('save')->once();
        $eventDispatcher->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(PurchaseOrderConfirmed::class));

        $service = new PurchaseOrderService($repo, $eventDispatcher);
        $service->confirm($poId);
    }
}
```

### Feature Test Standards

```php
// Every API endpoint must have a feature test
class PurchaseOrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_create_purchase_order_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/purchase-orders', []);
        $response->assertStatus(401);
    }

    public function test_create_purchase_order_validates_required_fields(): void
    {
        $response = $this->actingAs($this->authenticatedUser())
            ->postJson('/api/v1/purchase-orders', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id', 'order_date']);
    }

    public function test_create_purchase_order_returns_201(): void
    {
        // Setup, act, assert pattern
    }
}
```

### Required Test Coverage Per Module Component

| Component | Minimum Coverage |
|---|---|
| Domain Service (business logic) | 100% |
| Application Service | 90% |
| Repository (Eloquent) | Key CRUD methods feature-tested |
| Controller | All happy-path + validation error + auth |
| Event Listeners | Each listener unit-tested |
| Value Objects | All arithmetic, comparison, edge cases |

### Database Test Rules
- Use `RefreshDatabase` trait in feature tests
- Never use real tenant data in tests; always create via factories
- Test FK constraints explicitly: attempt orphan insert, expect constraint violation
- Test unique constraints: attempt duplicate insert, expect exception

---

## 11. SECURITY HARDENING CHECKLIST

Apply these to **every** controller, service, migration, and model:

### Authentication & Authorization
- [ ] All routes protected by `auth:api` (Passport) middleware
- [ ] All tenant-scoped resources checked: `$resource->tenant_id === $currentTenantId`
- [ ] Role/permission checked via Gate or policy before every sensitive operation
- [ ] API tokens scoped to minimum required permissions
- [ ] Device tokens validated on every multi-device request

### Tenant Isolation
- [ ] `ResolveTenant` middleware applied to all tenant-scoped API routes (reads `X-Tenant-ID` header)
- [ ] Repositories explicitly filter by `tenant_id` in all queries
- [ ] No raw queries that skip tenant filtering
- [ ] Background jobs carry `tenant_id` in the job payload for context restoration

### Input Validation
- [ ] All input validated in Form Request classes (never in controllers or services)
- [ ] Enum values validated against allowed list
- [ ] Monetary values validated as numeric, ≥ 0, max 20 digits
- [ ] Dates validated as valid dates, not in the future (where applicable)
- [ ] File uploads validated: mime type whitelist, max size, virus-scan hook available

### Data Security
- [ ] Sensitive fields (bank credentials, OAuth secrets) encrypted at application level using `encrypt()`/`decrypt()`
- [ ] Passwords hashed with bcrypt (never stored plaintext)
- [ ] PII fields (email, phone, addresses) not included in generic search indexes without explicit policy
- [ ] Audit logs capture IP address and user agent on every mutation

### SQL & Injection
- [ ] All queries via Eloquent or Query Builder — no raw string interpolation
- [ ] `whereRaw()` only with bound parameters, never string concatenation
- [ ] Pagination applied on all list endpoints (never `->get()` on unbounded collections)

### API Security
- [ ] Rate limiting applied per tenant and per IP
- [ ] CORS configured to whitelist known origins only
- [ ] All responses use JSON API Resources (never expose raw Eloquent models)
- [ ] Sensitive fields (passwords, `feed_credentials_enc`) excluded from all API resources
- [ ] HTTP 403 returned for authorization failures (not 404 — avoids resource enumeration)

---

## 12. API DESIGN & VERSIONING STRATEGY

### URL Structure
```
/api/v1/{module}/{resource}
/api/v1/{module}/{resource}/{id}
/api/v1/{module}/{resource}/{id}/{sub-resource}
```

### Examples
```
GET    /api/v1/purchase/orders
POST   /api/v1/purchase/orders
GET    /api/v1/purchase/orders/{id}
PUT    /api/v1/purchase/orders/{id}
DELETE /api/v1/purchase/orders/{id}
POST   /api/v1/purchase/orders/{id}/confirm
POST   /api/v1/purchase/orders/{id}/cancel
GET    /api/v1/purchase/orders/{id}/lines
POST   /api/v1/purchase/orders/{id}/lines
GET    /api/v1/inventory/stock-levels?product_id=&location_id=
POST   /api/v1/inventory/transfers
POST   /api/v1/finance/journal-entries/{id}/post
GET    /api/v1/finance/reports/balance-sheet?as_of=2025-12-31
```

### Versioning Rules
- **v1** — current stable API
- Introduce **v2** only for breaking changes (field removals, type changes, auth mechanism changes)
- Non-breaking additions (new fields, new endpoints) go into the current version
- Deprecated endpoints marked in Swagger with `@deprecated` and sunset date
- Old versions maintained for minimum 12 months after v(n+1) release

### Standard Response Envelopes

**Success (single resource):**
```json
{
  "data": { ... },
  "meta": { "version": "1.0" }
}
```

**Success (collection):**
```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 142,
    "last_page": 6
  },
  "links": {
    "first": "...", "last": "...", "prev": null, "next": "..."
  }
}
```

**Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "supplier_id": ["The supplier id field is required."]
  }
}
```

**Domain/Business Error:**
```json
{
  "error": {
    "code": "PERIOD_CLOSED",
    "message": "Cannot post to a closed fiscal period.",
    "context": { "period_id": 42, "period_name": "Dec 2024" }
  }
}
```

### Swagger Annotation Standard (L5-Swagger)

```php
/**
 * @OA\Post(
 *     path="/api/v1/purchase/orders",
 *     summary="Create a new purchase order",
 *     tags={"Purchase"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CreatePurchaseOrderRequest")),
 *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/PurchaseOrderResource")),
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Unauthorized")
 * )
 */
```

---

## 13. QUEUE JOBS CATALOG

All heavy-computation, side-effect, and notification tasks must be queued — never run synchronously in a request.

| Job | Queue | Priority | Trigger |
|---|---|---|---|
| `PostJournalEntryJob` | `finance` | high | GoodsReceiptPosted, ShipmentPosted, PaymentReceived |
| `UpdateStockLevelJob` | `inventory` | high | StockMovementRecorded |
| `UpdateCostLayerJob` | `inventory` | high | GoodsReceiptPosted, ReturnPosted |
| `GenerateDocumentNumberJob` | `default` | normal | Any document creation |
| `SendApprovalNotificationJob` | `notifications` | normal | ApprovalRequestCreated |
| `ImportBankTransactionsJob` | `finance` | low | Scheduled (cron: daily or webhook) |
| `MatchBankTransactionJob` | `finance` | low | BankTransactionImported |
| `RecallNotificationJob` | `notifications` | high | BatchStatusChanged(recalled) |
| `GenerateFinancialReportJob` | `reporting` | low | User requests report generation |
| `ExportAuditLogJob` | `reporting` | low | Compliance export request |
| `SyncBatchExpiryStatusJob` | `inventory` | low | Scheduled (cron: daily) |
| `SendInvoiceReminderJob` | `notifications` | normal | Scheduled (cron: daily, AR aging check) |

### Queue Configuration

```
Queues (in priority order):
  finance       → Redis, 3 workers, max_tries=3, retry_after=120s
  inventory     → Redis, 3 workers, max_tries=3, retry_after=60s
  notifications → Redis, 2 workers, max_tries=5, retry_after=90s
  reporting     → Redis, 1 worker,  max_tries=1, retry_after=600s
  default       → Redis, 2 workers, max_tries=3, retry_after=90s
```

---

## 14. REAL-TIME (REVERB) CHANNEL DEFINITIONS

Laravel Reverb broadcasts events to specific authenticated channels.

### Channel Types

| Type | Pattern | Auth |
|---|---|---|
| Private | `private-tenant.{tenantId}` | Passport token + tenant match |
| Private | `private-user.{userId}` | Passport token + user match |
| Presence | `presence-warehouse.{warehouseId}` | Passport token + permission check |

### Event → Channel Mapping

| Broadcast Event | Channel | Payload |
|---|---|---|
| `StockLevelUpdated` | `private-tenant.{tenantId}` | product_id, location_id, qty_on_hand, qty_available |
| `PurchaseOrderStatusChanged` | `private-tenant.{tenantId}` | order_id, old_status, new_status |
| `SalesOrderStatusChanged` | `private-tenant.{tenantId}` | order_id, old_status, new_status |
| `ApprovalRequestReceived` | `private-user.{userId}` | document_type, document_id, request_id |
| `InvoiceOverdue` | `private-user.{userId}` | invoice_id, party_name, amount, due_date |
| `BatchExpiringSoon` | `private-tenant.{tenantId}` | batch_id, product_name, expiry_date, days_remaining |
| `BatchRecalled` | `private-tenant.{tenantId}` | batch_id, product_name, affected_quantity |
| `FiscalPeriodClosed` | `private-tenant.{tenantId}` | period_id, period_name |
| `ScanEventReceived` | `presence-warehouse.{warehouseId}` | identifier_value, resolved_entity, location |

### Channel Authorization (in `routes/channels.php`)

```php
Broadcast::channel('tenant.{tenantId}', function (User $user, string $tenantId) {
    return $user->tenant_id === $tenantId;
});

Broadcast::channel('user.{userId}', function (User $user, string $userId) {
    return $user->id === $userId;
});

Broadcast::channel('warehouse.{warehouseId}', function (User $user, string $warehouseId) {
    return $user->can('warehouse.access', $warehouseId);
});
```

---

## 15. CODE GENERATION CHECKLISTS

### 15.1 Pre-Flight Checklist (BEFORE writing any code)

- [ ] Have I read all existing migrations for the affected module?
- [ ] Have I read all existing models, services, and repositories?
- [ ] Have I checked the complete module table index (Section 5) to avoid duplicating tables?
- [ ] Have I checked the domain event catalog (Section 7) to avoid missing event emissions?
- [ ] Have I confirmed the migration will be in the correct boot order (Section 8)?
- [ ] Have I resolved all naming inconsistencies (e.g., `tax_group_id` — not `tax_class_id`)?
- [ ] Have I confirmed the fiscal period is open before any financial event?
- [ ] Do I know which other modules will be affected by this change?

### 15.2 Post-Generation Checklist (AFTER writing code)

**Migration:**
- [ ] All foreign keys have explicit `->constrained()` or `->references()->on()` with `onDelete()` strategy
- [ ] All monetary/quantity fields are `DECIMAL(20,6)` (not float, not integer)
- [ ] All percentage fields are `DECIMAL(10,6)`
- [ ] All exchange rate fields are `DECIMAL(20,10)`
- [ ] All tenant-scoped tables have `tenant_id` as the first FK after `id`
- [ ] `timestamps()` called on every table
- [ ] `softDeletes()` on all master data tables
- [ ] Immutable tables (stock_movements, journal_entry_lines, trace_logs, audit_logs) have NO `softDeletes()`
- [ ] `down()` method fully reverses `up()` in correct reverse order

**Model:**
- [ ] Extends `Illuminate\Database\Eloquent\Model` directly
- [ ] Uses `HasAudit` trait (for audit logging)
- [ ] Uses `SoftDeletes` (for master data entities)
- [ ] `$guarded` set correctly (never `$fillable = ['*']`)
- [ ] All relationships defined and typed
- [ ] Casts defined for: `DECIMAL` fields (`'amount' => 'decimal:6'`), booleans, JSON, enums

**Repository:**
- [ ] Implements the corresponding interface from `Domain/RepositoryInterfaces/`
- [ ] All queries explicitly filter by `tenant_id`
- [ ] Pagination used on all collection-returning methods
- [ ] No N+1 queries (use `->with([...])` for eager loading)

**Application Service:**
- [ ] All database-mutating operations wrapped in `DB::transaction()`
- [ ] Domain events fired after successful transaction commit
- [ ] Returns DTOs — never raw Eloquent models
- [ ] All journal entries created for financial events

**Controller:**
- [ ] Uses Form Request for validation (no `$request->validate()` inline)
- [ ] Returns API Resource (not `->toArray()` or `->json()`)
- [ ] Uses correct HTTP status codes: 200, 201, 204, 400, 401, 403, 404, 422, 500
- [ ] Has Swagger `@OA\` annotations
- [ ] Dependency-injected via constructor (not `new Service()`)

**Event / Listener:**
- [ ] Event payload contains `tenant_id` for queued listeners to restore context
- [ ] Queued listeners implement `ShouldQueue`
- [ ] Listeners are idempotent (safe to retry on failure)
- [ ] Failure logged to `audit_logs` or dead-letter queue

---

## 16. ERROR HANDLING STANDARDS

### Exception Hierarchy

```
\Throwable
└── \Exception
    └── App\Modules\Shared\Exceptions\DomainException      (base for all business rule violations)
        ├── InsufficientStockException                       (Inventory)
        ├── PeriodClosedException                            (Finance)
        ├── CreditLimitExceededException                    (Customer)
        ├── NoPriceFoundException                           (Pricing)
        ├── ApprovalRequiredException                       (Shared)
        ├── DuplicateDocumentNumberException                (Shared)
        ├── InvalidDispositionException                     (Returns)
        └── TenantAccessDeniedException                     (Tenant isolation violation)
```

### Global Exception Handler

```php
// app/Exceptions/Handler.php
public function render($request, Throwable $e): Response
{
    if ($e instanceof DomainException) {
        return response()->json([
            'error' => [
                'code'    => $e->getCode() ?: class_basename($e),
                'message' => $e->getMessage(),
                'context' => $e->getContext(),   // e.g. period_id, product_id
            ]
        ], $e->getHttpStatus());  // DomainException carries HTTP status
    }

    if ($e instanceof \Illuminate\Validation\ValidationException) {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => $e->errors(),
        ], 422);
    }

    if ($e instanceof \Illuminate\Auth\AuthenticationException) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
        return response()->json(['message' => 'Forbidden.'], 403);
    }

    // Always log unexpected exceptions with full context
    Log::error($e->getMessage(), [
        'exception' => get_class($e),
        'trace'     => $e->getTraceAsString(),
        'tenant_id' => app(TenantContextService::class)->currentTenantId(),
        'user_id'   => auth()->id(),
    ]);

    return response()->json(['message' => 'Server error.'], 500);
}
```

### Transaction Rollback Pattern

```php
// In Application Services
public function postGoodsReceipt(GrnPostDto $dto): GrnResultDto
{
    return DB::transaction(function () use ($dto) {
        // 1. All DB writes
        $grn = $this->grnRepository->save(...);
        $this->stockMovementRepository->create(...);
        $this->stockLevelRepository->increment(...);
        $this->costLayerRepository->insert(...);

        // 2. Fire events AFTER all writes succeed
        // Events are dispatched after transaction commit via:
        event(new GoodsReceiptPosted($grn));

        return GrnResultDto::fromModel($grn);
    });
    // On exception: full rollback; no partial state
}
```

---

## 17. IMPLEMENTATION SEQUENCE

Implement modules in this order to respect dependencies and enable incremental testing:

```
Sprint 1 — Foundation
  1. Core (BaseModel, traits, reference tables, global utilities)
  2. Tenant (tenants, plans, settings)
  3. User (users, roles, permissions, Passport)
  4. OrganizationUnit

Sprint 2 — Finance Foundation (must exist for AR/AP linkage)
  5. Finance — Chart of Accounts + fiscal_years/periods only
     (journal entries come later, after transactions exist)

Sprint 3 — Master Data
  6. Customer
  7. Employee
  8. Supplier
  9. Product (categories, attributes, UoM, variants, identifiers)
  10. Pricing (price lists, resolution logic)
  11. Warehouse (warehouses, locations)

Sprint 4 — Inventory Core
  12. Inventory (batches, serials, stock_levels, stock_movements, cost_layers)
  13. Transfer Orders

Sprint 5 — Transactional
  14. Purchase (PO → GRN → Invoice → Payment → Return)
  15. Sales (SO → Shipment → Invoice → Payment → Return)

Sprint 6 — Finance Completion
  16. Finance — Journal entries, payments, bank feeds, reconciliation
  17. Tax rules and application

Sprint 7 — Cross-Cutting
  18. AIDC (trace_logs, scan interface, GS1 parsing)
  19. Numbering Sequences
  20. Approval Workflows
  21. Audit & Compliance (audit_logs observer)

Sprint 8 — Configuration & Reporting
  22. SMB feature flags
  23. Financial reports (Balance Sheet, P&L, AR/AP aging, Trial Balance)
  24. Real-time (Reverb channels)
  25. API documentation (Swagger annotations pass)

Sprint 9 — Hardening
  26. Security review (Section 11 checklist)
  27. Performance profiling (N+1 query elimination)
  28. Full test coverage pass
  29. Queue/worker configuration
```

---

## 18. SELF-CORRECTION PROTOCOL

When you detect an error, inconsistency, or design flaw during implementation:

### Step 1 — Classify
```
Is this a schema/migration error?      → Fix immediately; generate corrective migration
Is this a naming inconsistency?        → Fix in all affected files; document in AUDIT LOG
Is this a missing table/relationship?  → Define it, add to module table index (Section 5)
Is this a logic/business rule error?   → Fix in Domain layer; update unit tests
Is this a security vulnerability?      → Fix immediately; add to security checklist
Is this a performance issue?           → Flag with comment; add index or eager load
```

### Step 2 — Fix
- Fix the root cause, not just the symptom
- Propagate the fix to ALL affected files (models, migrations, services, tests, API resources)
- Never generate a workaround that masks the underlying issue

### Step 3 — Document
- Add a record to the AUDIT CORRECTIONS LOG at the top of the affected file
- Update the relevant section of SKILL.md or AGENT.md if the fix reveals a systemic issue
- Comment in the code: `// FIXED: [brief description] — was [old approach]`

### Step 4 — Verify
- Run the post-generation checklist (Section 15.2)
- Confirm no other occurrences of the same error exist elsewhere in the codebase
- If the error was schema-related, verify migration boot order (Section 8) is still valid

---

*End of AGENT.md — Version 1.0 (Enhanced, Complete, Production-Ready)*
*Read alongside: SKILL.md v2.0*
