# AGENT SKILL — Enterprise SaaS Multi-Tenant ERP/CRM Platform
**Role:** Autonomous Full-Stack Engineer & Principal Systems Architect  

---

## TABLE OF CONTENTS

1. [Agent Identity & Mission](#1-agent-identity--mission)
2. [Core Engineering Principles](#2-core-engineering-principles)
3. [Technology Stack](#3-technology-stack)
4. [Clean Architecture](#4-clean-architecture)
5. [Module Structure](#5-module-structure)
6. [Multi-Tenancy Strategy](#6-multi-tenancy-strategy)
7. [Database Design Standards](#7-database-design-standards)
8. [Module Specifications](#8-module-specifications)
   - 8.1 Core
   - 8.2 Tenant
   - 8.3 OrganizationUnit
   - 8.4 User
   - 8.5 Customer
   - 8.6 Employee *(new)*
   - 8.7 Supplier
   - 8.8 Product
   - 8.9 Pricing
   - 8.10 Warehouse
   - 8.11 Inventory
   - 8.12 Purchase
   - 8.13 Sales
   - 8.14 Finance *(corrected — fiscal tables added)*
   - 8.15 Shared
9. [Cross-Cutting Concerns](#9-cross-cutting-concerns)
   - 9.1 AIDC & Traceability
   - 9.2 Returns Management
   - 9.3 Audit & Compliance
   - 9.4 Search & Find
10. [Financial Accounting Standards](#10-financial-accounting-standards)
11. [Inventory Flow Logic](#11-inventory-flow-logic)
12. [Returns Management — Full Specification](#12-returns-management--full-specification)
13. [Multi-Price Support](#13-multi-price-support)
14. [Period-Based Accrual Accounting](#14-period-based-accrual-accounting)
15. [Traceability System](#15-traceability-system)
16. [SMB Flexibility](#16-smb-flexibility)
17. [Non-Functional Requirements](#17-non-functional-requirements)
18. [Industry Compliance & Standards](#18-industry-compliance--standards)
19. [Implementation Rules](#19-implementation-rules)
20. [Target Industries](#20-target-industries)

---

## 1. AGENT IDENTITY & MISSION

### Identity
Act as an autonomous Full-Stack Engineer and Principal Systems Architect.  
Before generating any code, thoroughly review, analyze, and fully understand all existing codebases, documentation, schemas, migrations, services, configurations, business rules, and architectural decisions.

### Mission
Design, refactor, and implement a fully dynamic, customizable, extendable, reusable, scalable, and production-ready enterprise SaaS multi-tenant ERP/CRM platform with:
- High performance and strong consistency
- Excellent developer experience
- Full auditability and compliance
- Alignment with SAP, Oracle, and Microsoft Dynamics ERP patterns
- GAAP/IFRS readiness for complex, multi-period financial operations

### Audit Mandate
Before writing any code, systematically identify and eliminate:
- Architectural flaws and SOLID violations
- Tight coupling and circular dependencies
- Security vulnerabilities and performance bottlenecks
- Weak typing, redundancy, inconsistencies, and all technical debt

---

## 2. CORE ENGINEERING PRINCIPLES

| Principle | Description |
|---|---|
| **SOLID** | Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion |
| **DRY** | Don't Repeat Yourself — eliminate all duplication |
| **KISS** | Keep It Simple — reduce unnecessary complexity |
| **High Cohesion** | Each module/class has a single, well-defined responsibility |
| **Loose Coupling** | Modules communicate via interfaces/contracts, never concrete dependencies |
| **Separation of Concerns** | Domain, Application, Infrastructure, Presentation are strictly separated |
| **Interface-Driven Design** | All cross-module dependencies use contracts defined in `Shared/` |
| **Event-Driven** | Side effects handled via domain events, not direct calls |
| **ACID Compliance** | All financial and inventory transactions are fully atomic |
| **3NF/BCNF** | All database schemas fully normalized |

---

## 3. TECHNOLOGY STACK

| Layer | Technology |
|---|---|
| Framework | Laravel (latest stable), PHP with strict types |
| Authentication | `laravel/passport` — OAuth2 for all actor types |
| Real-Time | `laravel/reverb` — WebSocket broadcasting |
| API Documentation | `darkaonline/l5-swagger` — OpenAPI 3.0 |
| Database | MySQL / PostgreSQL (fully normalized, ACID) |
| Queue | Laravel Queues (Redis recommended) |
| Events | Laravel Events + Listeners |
| File Storage | Laravel Storage — multipart/form-data attachments |
| AIDC | Unified adapter layer: 1D/2D barcodes, QR, RFID (HF/UHF), NFC, GS1 EPC |

---

## 4. CLEAN ARCHITECTURE

Each fully implemented module follows this layered structure:

```
app/Modules/<Module>/
├── Domain/
│   ├── Entities/              # Pure PHP domain objects
│   ├── RepositoryInterfaces/  # Persistence contracts
│   ├── Events/                # Domain events
│   ├── Exceptions/            # Domain-specific exceptions
│   └── ValueObjects/          # Immutable value types
├── Application/
│   ├── Contracts/             # Service interfaces
│   ├── Services/              # Service implementations
│   └── DTOs/                  # Data transfer objects
├── Infrastructure/
│   ├── Persistence/Eloquent/
│   │   ├── Models/            # Eloquent models (extend Model directly)
│   │   └── Repositories/     # Implements Domain repository interfaces
│   ├── Http/
│   │   ├── Controllers/       # Thin controllers delegating to services
│   │   ├── Resources/         # API resources
│   │   ├── Requests/          # Form request validation
│   │   └── Middleware/        # Module-specific middleware
│   ├── Providers/             # ServiceProvider (bindings, migrations, routes)
│   └── Broadcasting/         # Channel definitions (where applicable)
├── database/migrations/
└── routes/api.php
```

### Layer Dependency Rules
- **Domain** — minimal external dependencies; target is zero framework coupling (note: `Core/Domain/Events/BaseEvent.php` currently uses Illuminate broadcasting)
- **Application** — depends only on Domain contracts
- **Infrastructure** — implements Domain contracts using Eloquent / 3rd-party adapters
- **Cross-module communication** — via Events only; never direct class imports between modules

---

## 5. MODULE STRUCTURE

### Implementation Status

> **Design Decision:** Separate tables for `customers`, `employees`, and `suppliers`, each with a nullable `user_id` FK to the `users` table for portal/system access.

### Module Independence Rules
- Modules never import from each other's `Domain` or `Infrastructure` layers directly
- Cross-module communication uses contracts defined in `Shared/`
- Events published to the event bus are the only cross-module side-effect mechanism
- Each module is independently deployable, testable, and maintainable

---

## 6. MULTI-TENANCY STRATEGY

### Current Implementation
- Single database, tenant-scoped rows using `tenant_id` on all tenant-owned tables
- `ResolveTenant` middleware reads `X-Tenant-ID` from request headers
- Repositories filter `tenant_id` explicitly in queries

### Tenant Isolation Rules
- Repositories explicitly filter by `tenant_id` in all queries
- No tenant can read or write data belonging to another tenant
- Shared/global data (e.g., currency codes, country lists) lives in tenant-agnostic tables

### Hierarchical Structures
The platform natively supports recursive/nested data using adjacency list + materialized path:
- Product category trees
- Warehouse location hierarchies (Zone → Aisle → Rack → Shelf → Bin)
- Organization Unit trees (Company → Division → Department → Team)
- Account hierarchies in Chart of Accounts

---

## 7. DATABASE DESIGN STANDARDS

### Normalization
- All tables: minimum 3NF / BCNF
- No partial dependencies, no transitive dependencies, no repeating groups
- Junction/pivot tables for all M:N relationships
- Polymorphic references (`entity_type` + `entity_id`) used sparingly and intentionally

### Naming Conventions
| Convention | Rule |
|---|---|
| Tables | `snake_case`, plural (e.g., `order_lines`, `stock_movements`) |
| Primary Keys | `id BIGINT UNSIGNED AUTO_INCREMENT` |
| Foreign Keys | `<table_singular>_id` (e.g., `product_id`, `tenant_id`) with explicit DB-level constraints |
| Enums | Always define allowed values explicitly in migration |
| Timestamps | All tables include `created_at`, `updated_at`; soft-delete tables add `deleted_at` |
| Tenant Scope | Every tenant-scoped table has `tenant_id` as first FK after `id` |

### Precision Standards *(Corrected — unified)*
| Field Type | Precision |
|---|---|
| Monetary values | `DECIMAL(20,6)` |
| Quantities (UoM, stock) | `DECIMAL(20,6)` — supports fractional units |
| Exchange rates | `DECIMAL(20,10)` |
| Percentages | `DECIMAL(10,6)` |

### Constraints
- All foreign keys: explicit DB-level `FOREIGN KEY` constraints with defined `ON DELETE` strategy
- Unique keys where appropriate: `(tenant_id, code)`, `(tenant_id, reference_number)`
- JSON columns: used only for truly dynamic metadata; never for queryable/indexed fields
- Indexes: defined explicitly; composite indexes on `(tenant_id, <key_column>)`

### Migrations
- Located in: `app/Modules/<Module>/database/migrations/`
- One migration per logical schema change
- Always rollback-safe (`down()` method fully reverses `up()`)
- Foreign key creation in correct dependency order
- During the initial development phase, existing migrations may be modified instead of creating multiple incremental migrations

---

## 8. MODULE SPECIFICATIONS

---

### 8.1 Core

Responsibility: Shared kernel — base classes, traits, interfaces, and system bootstrap.

---

### 8.2 Tenant

Responsibility: Manage tenant lifecycle, plan/subscription, configuration, and custom domains.

Key Features:
- Tenant registration, activation, suspension
- Per-tenant feature flags and limits
- Custom domain support
- Configurable per tenant: default currency, timezone, date format, inventory valuation method, enabled optional modules, default warehouse, default accounts, approval workflows, numbering sequences

---

### 8.3 OrganizationUnit

Responsibility: Fully dynamic, customizable hierarchical organizational structure with configurable unit types.

Key Features:
- Recursive tree: adjacency list + materialized path for efficient subtree queries
- Configurable unit types per tenant (Company, Division, Branch, Department, Team, etc.)
- Used for: cost center assignments, access control scoping, reporting segmentation

---

### 8.4 User

Responsibility: Authentication, authorization, user profiles, roles, permissions, multi-device support.

Key Features:
- OAuth2 via Laravel Passport (all actor types: customer portal, supplier portal, staff)
- RBAC with direct permission overrides per user
- Multi-device token management
- Org-unit-scoped access control
- Real-time notifications via Laravel Reverb

---

### 8.5 Customer

Responsibility: Customer master data, AR ledger linkage, addresses, contacts, credit management.

Key Features:
- Customer portal access via unified auth (User module)
- Credit limit enforcement at order confirmation
- Automatic AR account linkage on creation
- Tiered pricing eligibility (linked to Pricing module)
- Full address book (billing, shipping, multiple entries)

---

### 8.6 Employee *(Added — was missing)*

Responsibility: Employee master data, payroll readiness, org-unit assignment, HR linkage.

Key Features:
- Linked to `users` for system access (optional — contract workers may not have logins)
- Org-unit assignment for cost center reporting
- Foundation for future Payroll module
- Soft-deleted on termination (historical records preserved)

---

### 8.7 Supplier

Responsibility: Supplier master data, AP ledger linkage, addresses, contacts, product catalogue linkage.

Key Features:
- Supplier portal access via unified auth
- Preferred supplier flag per product/variant
- Supplier-specific lead times and MOQ
- Automatic AP account linkage on creation

---

### 8.8 Product

Responsibility: Product catalog, types, variants, categories, units of measure, identifiers.

#### Product Types
| Type | Description |
|---|---|
| `physical` | Tangible goods; tracked in inventory |
| `service` | Non-stocked; no inventory movement |
| `digital` | Downloads/licenses; no physical inventory |
| `combo` | Bundle of other products; explodes on transaction |
| `variable` | Has configurable attributes generating variants |

Key Features:
- Recursive category trees with materialized path
- Full variant matrix (configurable attributes → auto-generated variant combinations)
- Multi-UoM: base, purchase, and sales UoMs with conversion factors
- Unified product identifier table covers all AIDC types (barcode, QR, RFID, NFC, GS1 EPC)
- Per-product account mapping for automatic journal entry generation
- Batch/lot/serial tracking flags (optional per product)
- Combo product explosion during transaction processing
- Configurable valuation method per product (overrides tenant default)

---

### 8.9 Pricing

Responsibility: Purchase and sales price lists, tiered pricing, customer/supplier-specific prices, time-bound validity.

#### Price Types
| Type | Description |
|---|---|
| `standard_purchase` | Default purchase cost |
| `standard_sales` | Default sales price |
| `wholesale` | For wholesale channel |
| `retail` | For retail/POS channel |
| `tier` | Quantity-break pricing |
| `customer_specific` | Negotiated price for a specific customer |
| `supplier_specific` | Agreed cost from a specific supplier |
| `promotional` | Time-limited discount pricing |

#### Price Resolution Order (highest priority first)
1. Customer/Supplier-specific price list item (quantity-range match)
2. Promotional price list item (within validity window)
3. Tiered price list item (quantity-break match)
4. Default price list item
5. Product standard price (fallback)

Key Features:
- Time-bound validity (`valid_from` / `valid_to`) on price lists and individual items
- UoM-specific pricing (price per each, per kg, per box)
- Multi-currency price lists
- Priority-based resolution (highest priority wins)
- Price history retained for audit

---

### 8.10 Warehouse

Responsibility: Physical warehouses, location hierarchies, zone configuration.

Location Hierarchy:
```
Warehouse
└── Zone (e.g., Cold Storage)
    └── Aisle (A)
        └── Rack (01)
            └── Shelf (1)
                └── Bin (001)
```

Key Features:
- Unlimited depth via self-referencing adjacency list + materialized path
- Location type flags (`is_pickable`, `is_receivable`) for workflow routing
- Quarantine and transit virtual warehouses for returns and in-transit stock
- Per-location capacity management (optional, configurable per tenant)

---

### 8.11 Inventory

Responsibility: Real-time stock levels, stock movements, cost layers, batch/lot/serial management, traceability, AIDC, allocation, cycle counting.

Key Features:
- `quantity_available = quantity_on_hand − quantity_reserved` (enforced at service layer, never stored)
- Configurable valuation methods: FIFO, LIFO, FEFO, Weighted Average, Specific Identification
- FIFO/FEFO layer selection is automatic on outbound movements
- Allocation algorithms: FIFO, FEFO (nearest expiry first), LIFO, nearest bin, manual override
- Cycle count produces `stock_movements` of type `adjustment_in/out` for full audit trail

---

### 8.12 Purchase

Responsibility: Full procurement cycle — purchase orders, goods receipt, purchase invoices, supplier payments, and purchase returns.

```
Purchase Order (PO) ──► Goods Receipt (GRN) ──► Purchase Invoice ──► Supplier Payment
                                                                  └──► Purchase Return ──► Debit Note
```
> **SMB Flexibility:** GRN without PO (direct buy) is supported — `po_id` is nullable on GRNs.

---

### 8.13 Sales

Responsibility: Full order-to-cash cycle — sales orders, shipments, sales invoices, customer payments, and sales returns.

```
Sales Order (SO) ──► Picking ──► Shipment ──► Sales Invoice ──► Customer Payment
                                                            └──► Sales Return ──► Credit Memo
```
> **SMB Flexibility:** Direct sell without SO is supported — shipments may be created without a linked SO.

---

### 8.14 Finance

Responsibility: Double-entry accounting, chart of accounts, journal entries, fiscal periods, bank feeds, tax, payments, and financial reporting.

#### Automatic Journal Entry Generation
Every transactional event generates a balanced journal entry automatically via event listeners.

| Business Event | Debit | Credit |
|---|---|---|
| Purchase Invoice posted | Inventory / Expense Account | Accounts Payable |
| Payment to supplier | Accounts Payable | Bank / Cash |
| Sales Invoice posted | Accounts Receivable | Sales Revenue |
| COGS on shipment | Cost of Goods Sold | Inventory |
| Receipt from customer | Bank / Cash | Accounts Receivable |
| Purchase return | Accounts Payable | Inventory / Expense |
| Sales return (restock) | Inventory | Sales Returns & Allowances |
| Inventory adjustment | Inventory Adjustment Expense | Inventory |
| Bank fee imported | Bank Fee Expense | Bank Account |
| Accrual entry | Expense / Revenue | Accrued Liabilities / Accrued Revenue |
| Prepaid expense | Prepaid Asset | Cash / Bank |

#### Financial Reports
- **Balance Sheet** — Assets = Liabilities + Equity (point-in-time)
- **Profit & Loss** — Revenue − COGS − Operating Expenses
- **Cash Flow Statement** — Operating / Investing / Financing activities
- **AR Aging** — Outstanding receivables by age bucket
- **AP Aging** — Outstanding payables by age bucket
- **Trial Balance** — All accounts with debit/credit totals
- **General Ledger** — Full transaction history per account
- **Tax Summary** — Tax collected and paid by period

---

### 8.15 Shared

Responsibility: Global reference tables and infrastructure bootstrap.

---

## 9. CROSS-CUTTING CONCERNS

---

### 9.1 AIDC & Traceability

Objective: Unified, technology-agnostic interface for all Automatic Identification and Data Capture technologies.

#### Supported Technologies
| Technology | Standards | Use Cases |
|---|---|---|
| 1D Barcode | EAN-8, EAN-13, UPC-A, UPC-E, Code 128, Code 39, ITF-14 | Retail, general inventory |
| 2D Barcode | QR Code, Data Matrix, PDF417 | URLs, serialized items |
| GS1-128 | Application Identifiers (GTIN, Batch AI-10, Serial AI-21, Expiry AI-17) | Pharma (DSCSA), logistics (EPCIS) |
| RFID HF | ISO 15693, ISO 14443 | Access control, item-level pharma |
| RFID UHF | ISO 18000-6C / EPC Gen2 | Retail inventory, bulk logistics |
| NFC | ISO 14443 / 18092 | High-value item authentication |
| GS1 EPC | SGTIN, SSCC, SGLN, GRAI, GIAI | Enterprise serialized traceability |

Identifier storage in `product_identifiers` table (Section 8.8).

#### Traceability Ledger (Immutable)

#### Scan-Based Operations
- Goods receiving (scan → GRN line)
- Pick and pack (scan → shipment line)
- Stock transfer (scan source → destination location)
- Cycle counting (scan → count sheet line)
- Returns receiving (scan → return line)

---

### 9.2 Returns Management

*(Full specification in Section 12)*

Returns are handled via dedicated tables in Purchase (8.12) and Sales (8.13) modules.
Cross-module credit memo table defined in Sales (8.13).

---

### 9.3 Audit & Compliance

Requirements:
- All entity mutations write to `audit_logs` (Laravel Observer pattern on all models)
- Immutable after write
- Supports before/after diff for all field changes
- Linked to tenant, user, and timestamp
- Exportable for compliance reports (CSV, PDF)

---

### 9.4 Search & Find System (AIDC Lookup)

Purpose: Comprehensive `findBy` capability across all ERP entities using any identifier.

#### Supported Lookups
| Identifier | Entities Returned |
|---|---|
| Barcode (1D/2D/QR) | Product, Variant, Batch, Serial, Location |
| RFID Tag | Product, Variant, Serial, Asset |
| GS1 EPC | Serialized item with full traceability chain |
| SKU / Product Code | Product, Variant |
| Batch Number | Batch → stock level, expiry, location |
| Serial Number | Serial → status, location, history |
| Order Reference | Order → lines, status, party |
| Party Code / Tax Number | Customer or Supplier |
| Location Code | Warehouse location → current stock |

#### Unified Search Interface
```php
// In Shared/Contracts/
interface FindByIdentifierInterface {
    public function find(string $identifierValue, ?string $technology = null): IdentifierResult;
    // Returns: entity_type, entity_id, resolved entity data,
    //          current stock quantity and location,
    //          last trace_log entry, relevant pricing
}
```

Features:
- Always tenant-scoped
- Cross-entity lookup from a single scan (barcode → product + batch + stock level + price)
- Supports partial/prefix-based lookup
- Supports multiple AIDC technologies returning the same entity

---

## 10. FINANCIAL ACCOUNTING STANDARDS

### Double-Entry Rules
- Every financial event produces a journal entry with balanced debit and credit lines
- `SUM(debit lines) == SUM(credit lines)` enforced at application layer AND database constraint
- No posted journal entry may be modified — reversal only via a counter journal entry
- All journal entries assigned to a `fiscal_period_id` at posting time

### Account Normal Balances
| Account Type | Increases With | Decreases With |
|---|---|---|
| Asset | Debit | Credit |
| Liability | Credit | Debit |
| Equity | Credit | Debit |
| Revenue | Credit | Debit |
| Expense | Debit | Credit |

### Period Control
| Period Status | Allowed Actions |
|---|---|
| Open | Normal posting |
| Closed | Posting requires elevated permission |
| Locked | No posting, not even by admins |

### Year-End Close Process
1. Auto-reverse open accrual entries
2. Transfer net income to Retained Earnings
3. Lock the fiscal year
4. Open the next fiscal year

---

## 11. INVENTORY FLOW LOGIC

### Inbound (Purchasing / Receiving)
1. Purchase Order created → status: `draft`
2. PO confirmed → status: `confirmed`
3. GRN created, linked to PO line(s) — batch/lot/serial assigned here
4. `stock_movements` record: type = `receipt`
5. `stock_levels` updated: `quantity_on_hand` increases
6. `inventory_cost_layers` record created
7. Journal entry: Debit Inventory / Credit Accounts Payable

> SMB Option: GRN without PO — `po_id` nullable on `grn_headers`

### Outbound (Sales / Shipping)
1. Sales Order created → status: `draft`
2. SO confirmed → `stock_reservations` created per line
3. Picking: items allocated from specific batch/serial/location per allocation strategy
4. Packing → `shipments` record created
5. Ship → `stock_movements` record: type = `shipment`
6. `stock_levels` updated: `quantity_on_hand` decreases, `quantity_reserved` decreases
7. Journal: Debit COGS + Debit AR, Credit Inventory + Credit Revenue

> SMB Option: Direct sell without SO — direct shipment creation supported

### Allocation Strategies
| Strategy | Description |
|---|---|
| FIFO | First In, First Out (default) |
| LIFO | Last In, First Out |
| FEFO | First Expired, First Out (required for pharma/food) |
| Manual | User explicitly selects batch/serial/location |

### Stock Adjustment
- Cycle count variances create `stock_adjustment` records
- Approved adjustments post `stock_movements` of type `adjustment_in` or `adjustment_out`
- Journal entry: Debit/Credit Inventory Adjustment Expense account

---

## 12. RETURNS MANAGEMENT — FULL SPECIFICATION

### Purchase Returns (to Supplier)

| Scenario | Supported |
|---|---|
| Full return of a GRN | ✅ |
| Partial return (lines or qty) | ✅ |
| Return with original batch/lot/serial ref | ✅ |
| Return without batch/lot/serial ref | ✅ |
| Return of damaged/expired goods | ✅ |
| Return after invoice already paid | ✅ |

**Workflow:**
1. Create `purchase_return` header (optionally reference GRN/invoice)
2. Add return lines: product/variant, qty, condition, batch/lot/serial (optional)
3. Approve → triggers quality check workflow (optional)
4. Disposition decision per line: `restock` | `scrap` | `vendor_return`
5. Post → triggers:
   - `stock_movements` type = `return_out` (removes from warehouse)
   - `inventory_cost_layers` adjustment aligned with original valuation method
   - Debit note generation (AP credit)
   - Journal: Dr Accounts Payable / Cr Inventory or Expense

### Sales Returns (from Customer)

| Scenario | Supported |
|---|---|
| Full return of SO / shipment | ✅ |
| Partial return (lines or qty) | ✅ |
| Return with original batch/lot/serial ref | ✅ |
| Return without batch/lot/serial ref | ✅ |
| Return of damaged goods (not restockable) | ✅ |
| Return after invoice paid (refund vs credit memo) | ✅ |

**Workflow:**
1. Create `sales_return` header (optionally reference SO/invoice)
2. Add return lines: product/variant, qty, condition, batch/lot/serial (optional)
3. Approve → triggers quality check workflow (optional)
4. Disposition per line: `restock` | `scrap` | `quarantine`
5. Restocking fee calculated (optional, configurable per product/customer)
6. Post → triggers:
   - `stock_movements` type = `return_in` (adds back if restocked)
   - `inventory_cost_layers` re-insertion (at original cost or current cost, configurable)
   - Credit memo generation (AR debit)
   - Journal: Dr Sales Returns & Allowances (+restocking fee) / Cr AR

### Return Dispositions
| Condition | Default Disposition |
|---|---|
| Good | Restock |
| Damaged | Quarantine or Scrap |
| Expired | Scrap |
| Defective | Quarantine or Vendor Return |

### Inventory Cost Alignment on Returns
| Valuation Method | Return Layer Treatment |
|---|---|
| FIFO / FEFO | Returned layers re-inserted at original layer cost |
| Weighted Average | Weighted average recalculated after return |
| Specific Identification | Original cost from serial/batch record used |

---

## 13. MULTI-PRICE SUPPORT

```
Price Types:
- standard_purchase    (default purchase cost from supplier)
- standard_sales       (default selling price)
- wholesale            (wholesale channel pricing)
- retail               (retail/POS channel pricing)
- tier                 (quantity-break pricing)
- customer_specific    (negotiated price for specific customer)
- supplier_specific    (agreed cost from specific supplier)
- promotional          (time-limited discount pricing)
```

Features:
- Time-bound validity (`valid_from` / `valid_to`) on all price entries
- UoM-specific pricing (price per each, per kg, per box)
- Multi-currency price lists
- Priority-based resolution (highest priority wins — see Section 8.9)
- Configurable rounding rules per tenant
- Price history fully retained for audit

---

## 14. PERIOD-BASED ACCRUAL ACCOUNTING

Requirements:
- Revenue and expenses recognized when earned/incurred, not when cash moves
- All journal entries assigned to a `fiscal_period_id` at posting time
- Periods have status: `open`, `closed`, or `locked`
- Back-dating configurable per tenant (allow/disallow posting to prior periods)
- Year-end close: transfer net income to retained earnings; open next fiscal year
- Multi-year fiscal calendar supported (`fiscal_years` + `fiscal_periods` tables)

Accrual Entry Examples:
- **Revenue accrual:** Dr Accrued Revenue (AR) / Cr Revenue
- **Expense accrual:** Dr Expense / Cr Accrued Liabilities
- **Prepaid expense:** Dr Prepaid Asset / Cr Cash — amortize each period

---

## 15. TRACEABILITY SYSTEM

### Traceability Chain
```
Product → Variant → Batch/Lot → Serial → Location
```

### Forward Traceability (track where it went)
- From receipt → through all internal transfers → to final sale/disposal
- Enables: delivery confirmation, recall notification

### Backward Traceability (track where it came from)
- From any item in the field → back to supplier, GRN, batch, manufacture date
- Enables: root cause analysis, regulatory reporting

### Trace Log Events (all write to `trace_logs`)
- Goods received, Internal transfer, Picking, Packing, Shipping
- Sales delivery, Return received, Adjustment, Disposal/Scrap
- Barcode/RFID scan events (any technology)

### Industry Compliance
| Standard | Supported | Use Case |
|---|---|---|
| DSCSA | ✅ | Pharmaceutical drug supply chain (US) |
| EPCIS 2.0 | ✅ | Logistics event data sharing |
| GS1 SSCC | ✅ | Shipping container identification |
| ISO 15693 / 14443 | ✅ | RFID HF tag standards |
| ISO 18000-6C (EPC Gen2) | ✅ | RFID UHF tag standards |

---

## 16. SMB FLEXIBILITY

Optional features enabled/disabled per tenant via `tenant_settings`:

| Feature | Enterprise Default | SMB Option |
|---|---|---|
| Purchase Order required before GRN | Required | Optional (direct buy) |
| Sales Order required before Shipment | Required | Optional (direct sale) |
| Batch tracking | Enabled | Optional |
| Lot tracking | Enabled | Optional |
| Serial tracking | Enabled | Optional |
| Multi-UoM | Enabled | Optional (single UoM only) |
| GS1 / RFID | Configurable | Optional |
| Approval workflows | Required | Optional |
| Multi-warehouse | Enabled | Optional (single warehouse) |
| Quality check on returns | Required | Optional |

**Implementation rule:** All optional features are gated by tenant configuration checks in the Application Service layer — never by database schema changes.

---

## 17. NON-FUNCTIONAL REQUIREMENTS

| Requirement | Standard |
|---|---|
| Performance | API responses < 200ms for standard queries; background jobs for heavy computation |
| Scalability | Horizontal scaling; stateless API; Redis-backed queue-based async processing |
| Security | OAuth2 (Passport), tenant isolation via global Eloquent scope, input validation on all requests, Eloquent for SQL injection prevention, encrypted sensitive fields (bank credentials) |
| Availability | Designed for high availability; no single point of failure in data layer |
| Maintainability | Clean architecture enforced; all business logic in Domain/Application layers, never in controllers |
| Testability | Repositories and Services fully unit-testable via interfaces; feature tests for all endpoints |
| Observability | Structured logging, audit logs, event tracking, query performance monitoring |
| API Documentation | All endpoints documented via L5-Swagger OpenAPI 3.0 annotations |
| Real-Time | Laravel Reverb: live inventory updates, order status changes, financial alerts |
| Data Integrity | ACID-compliant transactions; DB-level FK constraints; decimal precision enforced |

---

## 18. INDUSTRY COMPLIANCE & STANDARDS

| Standard | Module | Requirement |
|---|---|---|
| GAAP / IFRS | Finance | Double-entry, period-based, accrual accounting |
| DSCSA | AIDC + Inventory | Serialized drug tracking (US pharma supply chain) |
| EPCIS 2.0 | AIDC | Event-based supply chain visibility sharing |
| GS1 Standards | AIDC, Product | GTIN, SSCC, EPC, AI code parsing |
| SAP Alignment | All | Business Partner, Material Master, Movement Types patterns |
| Oracle Alignment | All | Item Master, Subledger Accounting patterns |
| MS Dynamics Alignment | All | Product families, item tracking groups patterns |
| PCI-DSS | Finance | Secure handling of payment card data |
| ISO 15693 / 14443 | AIDC | RFID HF tag communication standards |
| ISO 18000-6C | AIDC | RFID UHF (EPC Gen2) tag standard |

---

## 19. IMPLEMENTATION RULES

These rules apply to every code generation and architecture decision:

1. **Always read before writing.** Review all existing code, migrations, and context before generating anything new.
2. **Never break existing functionality.** All refactors must preserve backward compatibility or document a migration path.
3. **One module, one concern.** Never put Module A's business logic into Module B. Use events for cross-module effects.
4. **Interfaces first.** Define repository and service interfaces in Domain layer before implementing in Infrastructure.
5. **Never bypass tenant scope.** Every query must filter by `tenant_id`. Currently implemented via `ResolveTenant` middleware reading `X-Tenant-ID` header, with repositories filtering explicitly.
6. **No raw SQL for business logic.** Use Eloquent with relationships; raw SQL only for performance-critical reporting queries.
7. **Every financial event = journal entry.** No stock or order operation may affect financial state without a corresponding balanced, posted journal entry.
8. **Migrations are forward-only in production.** The `down()` method must exist but production rollbacks require a new migration.
9. **DECIMAL(20,6) for all monetary and quantity values.**.
10. **Soft deletes on all master data.** Products, customers, suppliers, employees, accounts, warehouses — use `deleted_at`, not hard deletes.
11. **Audit everything.** All create, update, delete, approve, reject, post, void actions must write to `audit_logs`.
12. **Events over direct calls.** When an order is confirmed, fire `OrderConfirmed` event — let Inventory, Finance, and Notification listeners react independently.
13. **Validate at the boundary.** All validation in Form Request classes (Presentation layer). Domain objects assume valid data.
14. **Return DTOs, not Eloquent models, from Application Services.** Keeps Presentation layer decoupled from ORM.
15. **Fix all, document all.** If a bug, inconsistency, or design flaw is discovered during implementation, fix it immediately and note it in a comment or changelog — do not defer technical debt.
16. **Immutable ledgers.** `stock_movements`, `journal_entry_lines`, `trace_logs`, and `audit_logs` are append-only. No updates or deletes permitted on these tables.
17. **Strict PHP types.** `declare(strict_types=1)` in every file.
18. **No direct inter-module imports.** All cross-module dependencies must go through contracts in the `Shared/` module.

---

## 20. TARGET INDUSTRIES

This platform is designed to serve (but is not limited to):

| Industry | Key Capabilities Used |
|---|---|
| Pharmacy / Healthcare | DSCSA, batch/serial tracking, FEFO, cold chain, RFID |
| Manufacturing | Bill of materials, lot tracking, cycle counting, cost layers |
| eCommerce | Multi-warehouse, variant products, digital products, shipments |
| Retail / POS | Real-time stock, barcode scanning, price lists, multi-UoM |
| Wholesale | Tiered pricing, credit limits, batch orders, AR/AP aging |
| Warehouse / Logistics | EPCIS, SSCC, location hierarchies, transfer orders, RFID UHF |
| Renting | Serial tracking, condition management, return workflows |
| Hospitals | DSCSA, serial/batch tracking, quarantine handling |
| Service Centers | Service products, warranty tracking, returns |
| Supermarkets / Grocery | FEFO, perishables, batch expiry, POS integration |

---
