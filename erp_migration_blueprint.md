# ERP Migration Blueprint

## Scope
A normalized, tenant-aware, modular migration design for a full ERP/CRM platform. Migrations are organized under:

`app/Modules/<Module>/database/migrations/*_table.php`

The schema is designed for:
- SaaS multi-tenancy
- Unified party model for customers, suppliers, employees, and stakeholders
- Procurement, sales, inventory, warehouse, returns, finance, pricing, tax, AIDC, and audit
- Batch, lot, serial, barcode, RFID, NFC, and GS1 compatibility
- Period-based accounting and double-entry posting
- Optional SMB shortcuts such as direct buy/sell and GRN without PO

## Global schema rules
- Every tenant-scoped table includes `tenant_id`
- Every document table includes: `document_number`, `document_date`, `status`, `currency_id`, `exchange_rate`, `notes`, `metadata`
- Monetary values use `decimal(18,4)`
- Quantity values use `decimal(18,4)`
- Use `softDeletes()` only where business rules allow recovery
- Use immutable posted financial records
- Use foreign keys everywhere, no free-text references for core relations
- Use recursive closure table or parent path for hierarchical entities
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

## 2. Party Module
### Migrations
- `parties_table`
- `party_addresses_table`
- `party_contacts_table`
- `party_identifiers_table`
- `party_tax_profiles_table`
- `party_relationships_table`

### Party model supports
- Customer
- Supplier
- Employee
- Courier
- Warehouse contact
- Other business stakeholders

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

## 8. Procurement Module
### Migrations
- `purchase_orders_table`
- `purchase_order_lines_table`
- `goods_receipts_table`
- `goods_receipt_lines_table`
- `supplier_invoices_table`
- `supplier_invoice_lines_table`
- `purchase_payments_table`

### SMB flexibility
- `purchase_orders_table` may be optional for `goods_receipts_table`
- Direct buy is supported by allowing GRN to reference a party without PO

## 9. Sales Module
### Migrations
- `sales_orders_table`
- `sales_order_lines_table`
- `shipments_table`
- `shipments_lines_table`
- `sales_invoices_table`
- `sales_invoice_lines_table`
- `customer_payments_table`

### SMB flexibility
- Direct sell is supported by allowing shipment/invoice without SO

## 10. Returns Module
### Migrations
- `purchase_returns_table`
- `purchase_return_lines_table`
- `sales_returns_table`
- `sales_return_lines_table`
- `credit_notes_table`
- `debit_notes_table`
- `return_inspections_table`
- `return_dispositions_table`

### Supports
- Partial returns
- Returns with or without original batch/lot/serial references
- Restocking
- Scrap
- Vendor return
- Quality checks
- Restocking fees
- Credit memo generation

## 11. Finance Module
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

## 12. AIDC Module
### Migrations
- `identifier_types_table`
- `entity_identifiers_table`
- `scan_sessions_table`
- `scan_events_table`

### Supports
- 1D barcode
- 2D barcode
- QR code
- RFID HF/UHF
- NFC
- GS1 EPC

## 13. Audit and Compliance Module
### Migrations
- `compliance_events_table`
- `audit_trails_table`
- `data_retention_policies_table`
- `regulatory_mappings_table`

## 14. Shared Reference Tables
### Migrations
- `currencies_table`
- `exchange_rates_table`
- `document_sequences_table`
- `statuses_table`
- `lookup_values_table`
- `metadata_definitions_table`

## Recommended implementation conventions

### Naming
- Tables use plural snake_case
- FK columns end in `_id`
- Closure tables use `ancestor_id` and `descendant_id`
- Document lines reference their header using `{header}_id`

### Indexing
- Unique index on `tenant_id + document_number`
- Composite index on `tenant_id + status + document_date`
- Composite index on `tenant_id + party_id`
- Composite index on `tenant_id + warehouse_location_id`
- Composite index on `tenant_id + product_id + batch_lot_id + serial_number_id`

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
1. Core
2. Shared references
3. Party
4. Product
5. Pricing
6. Tax
7. Warehouse
8. Inventory
9. Procurement
10. Sales
11. Returns
12. Finance
13. AIDC
14. Audit and Compliance

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

