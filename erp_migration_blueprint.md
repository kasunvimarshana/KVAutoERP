# ERP Migration Blueprint

> **Status:** This is an early design document. The actual schema has been implemented across
> 66 module-scoped migrations in `app/Modules/<Module>/database/migrations/`.
> For the authoritative specification, see [SKILL.md](SKILL.md) (module schemas)
> and [MIGRATIONS_DEFERRED_FK_MATRIX.md](MIGRATIONS_DEFERRED_FK_MATRIX.md) (cross-module FKs).
>
> **Key differences from this blueprint:**
> - The "Party" module was replaced by separate `Customer`, `Employee`, and `Supplier` modules
> - Monetary/quantity values use `DECIMAL(20,6)` (not `decimal(18,4)`)
> - OrgUnit uses materialized path hierarchy (not closure table)
> - Constraint naming follows `{table}_{column(s)}_{type}` with `_pk`, `_uk`, `_idx`, `_fk` suffixes

## Scope
A normalized, tenant-aware, modular migration design for a full ERP/CRM platform. Migrations are organized under:

`app/Modules/<Module>/database/migrations/*_table.php`

The schema is designed for:
- SaaS multi-tenancy
- Separate entity tables for customers, suppliers, and employees (each with optional `user_id` FK)
- Procurement, sales, inventory, warehouse, returns, finance, pricing, tax, and audit
- Batch, lot, serial, and barcode compatibility
- Period-based accounting and double-entry posting
- Optional SMB shortcuts such as direct buy/sell and GRN without PO

## Global schema rules
- Every tenant-scoped table includes `tenant_id`
- Every document table includes: `document_number`, `document_date`, `status`, `currency_id`, `exchange_rate`, `notes`, `metadata`
- Monetary values use `DECIMAL(20,6)`
- Quantity values use `DECIMAL(20,6)`
- Use `softDeletes()` only where business rules allow recovery (8 models currently use it)
- Use immutable posted financial records
- Use foreign keys everywhere, no free-text references for core relations
- OrgUnit uses materialized path for hierarchy; product categories use `parent_id`
- Use nullable source-document references for direct and linked flows

## 1. Core Module
### Migrations
- `tenants_table`
- `users_table`
- `roles_table`
- `permissions_table`
- `role_permissions_table`
- `user_roles_table`
- `organization_units_table`
- `organization_unit_closure_table`
- `tenant_settings_table`
- `attachments_table`
- `audit_logs_table`
- `domain_outbox_table`

### Purpose
- Tenant isolation
- Unified auth and authorization
- Recursive organization hierarchy
- File attachments
- Audit trail
- Reliable event dispatching

## 2. Customer / Employee / Supplier Modules
### Migrations (per module)
- `customers_table` / `employees_table` / `suppliers_table`
- `customer_addresses_table` / `employee_addresses_table` / `supplier_addresses_table`
- `customer_contacts_table` / `employee_contacts_table` / `supplier_contacts_table`
- `supplier_products_table` (Supplier only)

### Design
- Separate tables per entity type (not a unified "party" model)
- Each has an optional nullable `user_id` FK to `users` for portal/system access
- Customers link to AR accounts; Suppliers link to AP accounts
- Currently migration-only stubs (no application code)

## 3. Product Module
### Migrations
- `product_categories_table`
- `product_category_closure_table`
- `products_table`
- `product_variants_table`
- `product_attributes_table`
- `product_attribute_values_table`
- `product_variant_attributes_table`
- `units_of_measure_table`
- `product_uom_conversions_table`
- `product_identifiers_table`
- `product_barcodes_table`

### Supports
- Physical, service, digital, combo, and variable products
- Multi-UoM
- Product identifiers and standard codes
- Variant-level traceability

## 4. Pricing Module
### Migrations
- `price_lists_table`
- `price_list_items_table`
- `price_list_rules_table`
- `customer_price_list_assignments_table`

### Supports
- Multiple buy/sell prices
- Customer-specific pricing
- Time-based pricing windows
- Currency-aware price rules

## 5. Tax Module
### Migrations
- `tax_groups_table`
- `tax_rates_table`
- `tax_rules_table`
- `tax_exemptions_table`

## 6. Warehouse Module
### Migrations
- `warehouses_table`
- `warehouse_locations_table`
- `warehouse_location_closure_table`
- `warehouse_types_table`

### Supports
- Warehouse hierarchy
- Bin/location nesting
- Multi-site operations

## 7. Inventory Module
### Migrations
- `stock_items_table`
- `stock_layers_table`
- `stock_movements_table`
- `stock_reservations_table`
- `stock_adjustments_table`
- `stock_counts_table`
- `stock_count_lines_table`
- `batch_lots_table`
- `serial_numbers_table`
- `inventory_allocation_rules_table`
- `inventory_reconciliation_runs_table`

### Supports
- FIFO/LIFO/weighted average readiness
- Batch/lot/serial traceability
- Expiry tracking
- Stock reservation and allocation
- Reconciliation and cycle counts

## 8. Purchase Module
### Migrations
- `purchase_orders_table`
- `purchase_order_lines_table`
- `grn_headers_table`
- `grn_lines_table`
- `purchase_invoices_table`
- `purchase_invoice_lines_table`
- `purchase_returns_table`
- `purchase_return_lines_table`

### SMB flexibility
- `purchase_orders_table` may be optional for `grn_headers_table`
- Direct buy is supported by allowing GRN to reference a supplier without PO

## 9. Sales Module
### Migrations
- `sales_orders_table`
- `sales_order_lines_table`
- `shipments_table`
- `shipment_lines_table`
- `sales_invoices_table`
- `sales_invoice_lines_table`
- `sales_returns_table`
- `sales_return_lines_table`

### SMB flexibility
- Direct sell is supported by allowing shipment/invoice without SO

> **Note:** Returns are handled within their respective modules (Purchase and Sales),
> not as a standalone Returns module. Credit/debit notes are handled within Finance.

## 10. Finance Module
### Migrations
- `fiscal_years_table`
- `fiscal_periods_table`
- `account_groups_table`
- `chart_of_accounts_table`
- `journal_entries_table`
- `journal_entry_lines_table`
- `bank_accounts_table`
- `credit_cards_table`
- `bank_statement_imports_table`
- `bank_statement_lines_table`
- `transaction_rules_table`
- `transaction_reclassifications_table`
- `financial_snapshots_table`

### Supports
- Double-entry accounting
- Accrual accounting
- Correct fiscal period assignment
- Balance Sheet and Profit & Loss reporting
- Bank and credit card import
- Bulk reclassification

## 11. Audit Module
### Migrations
- `audit_logs_table`

### Supports
- Immutable audit trail
- Field-level change tracking via HasAudit trait (used by 20 models)
- IP address and user agent capture

## 12. Configuration Module
### Migrations
- `module_configurations_table`

## 13. Shared Module
### Migrations
- `global_reference_tables` (currencies, countries, timezones, languages)
- `add_remaining_foreign_keys` (deferred cross-module FKs)

## Recommended implementation conventions

### Naming
- Tables use plural snake_case
- FK columns end in `_id`
- Closure tables use `ancestor_id` and `descendant_id`
- Document lines reference their header using `{header}_id`

### Indexing
- Unique index on `tenant_id + document_number`
- Composite index on `tenant_id + status + document_date`
- Composite index on `tenant_id + entity_id` (customer_id, supplier_id, etc.)
- Composite index on `tenant_id + warehouse_location_id`
- Composite index on `tenant_id + product_id + batch_id + serial_id`

### Posting rules
- Inventory movements create ledger-ready events
- Financial postings are generated from posted business documents only
- Posted journal entries are immutable
- Reversals are handled by counter-entry

### Normalization strategy
- Master data isolated from transactions
- Lookup tables separated from transactional records
- Avoid duplicated denormalized totals except for reporting caches
- Use event-driven read models for dashboards

## Suggested migration order
1. Shared (global reference tables)
2. Core
3. Tenant
4. Auth
5. User
6. OrganizationUnit
7. Customer / Employee / Supplier
8. Product
9. Pricing
10. Tax
11. Warehouse
12. Inventory
13. Purchase
14. Sales
15. Finance
16. Configuration
17. Audit
18. Shared (deferred cross-module FKs — must run last)

## Implementation pattern for each migration
Each table migration should follow this structure:
- Create table
- Add primary key
- Add tenant foreign key when required
- Add business foreign keys
- Add audit timestamps
- Add soft deletes only when allowed
- Add indexes
- Add unique constraints
- Add foreign key constraints with cascade behavior only where appropriate

## Core design resolution
The system should not use one giant transaction table for everything. Instead, use:
- Normalized document headers
- Normalized document lines
- Shared references to direct or linked source documents
- Posted financial and inventory events generated from those documents

This keeps the design auditable, scalable, and consistent with enterprise ERP patterns.

