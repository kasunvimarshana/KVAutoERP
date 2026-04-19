# Deferred FK Matrix

This document maps cross-module foreign keys that were moved out of early module migrations into the late shared migration:
`database/migrations/2024_01_01_999999_add_remaining_foreign_keys.php`.

## Why This Exists

Early migrations (by timestamp) were creating foreign keys to tables introduced later by other modules. That can break fresh `migrate` runs depending on migration order. The fix keeps FK columns in place and defers only the cross-module constraints.

## Source Migrations Updated

- app/Modules/Purchase/database/migrations/2024_01_01_100001_create_purchase_orders_table.php
- app/Modules/Purchase/database/migrations/2024_01_01_100002_create_purchase_order_lines_table.php
- app/Modules/Purchase/database/migrations/2024_01_01_100003a_create_grn_headers_table.php
- app/Modules/Purchase/database/migrations/2024_01_01_100004a_create_purchase_invoices_table.php
- app/Modules/Purchase/database/migrations/2024_01_01_100005a_create_purchase_returns_table.php
- app/Modules/Sales/database/migrations/2024_01_01_110001_create_sales_orders_table.php
- app/Modules/Sales/database/migrations/2024_01_01_110002_create_sales_order_lines_table.php
- app/Modules/Sales/database/migrations/2024_01_01_110003a_create_shipments_table.php
- app/Modules/Sales/database/migrations/2024_01_01_110004a_create_sales_invoices_table.php
- app/Modules/Sales/database/migrations/2024_01_01_110005a_create_sales_returns_table.php
- app/Modules/Finance/database/migrations/2024_01_01_120003_create_journal_entries_table.php

## Deferred FK Definitions

| Domain | Table | Column | References | On Delete |
| --- | --- | --- | --- | --- |
| Purchase | purchase_orders | supplier_id | suppliers(id) | cascade |
| Purchase | purchase_orders | org_unit_id | org_units(id) | set null |
| Purchase | purchase_orders | warehouse_id | warehouses(id) | cascade |
| Purchase | purchase_orders | created_by | users(id) | default |
| Purchase | purchase_orders | approved_by | users(id) | set null |
| Purchase | purchase_order_lines | product_id | products(id) | cascade |
| Purchase | purchase_order_lines | variant_id | product_variants(id) | set null |
| Purchase | purchase_order_lines | uom_id | units_of_measure(id) | default |
| Purchase | purchase_order_lines | tax_group_id | tax_groups(id) | set null |
| Purchase | purchase_order_lines | account_id | accounts(id) | set null |
| Purchase | grn_headers | supplier_id | suppliers(id) | cascade |
| Purchase | grn_headers | warehouse_id | warehouses(id) | cascade |
| Purchase | grn_headers | created_by | users(id) | default |
| Purchase | grn_lines | product_id | products(id) | cascade |
| Purchase | grn_lines | variant_id | product_variants(id) | set null |
| Purchase | grn_lines | batch_id | batches(id) | set null |
| Purchase | grn_lines | serial_id | serials(id) | set null |
| Purchase | grn_lines | location_id | warehouse_locations(id) | cascade |
| Purchase | grn_lines | uom_id | units_of_measure(id) | default |
| Purchase | purchase_invoices | supplier_id | suppliers(id) | cascade |
| Purchase | purchase_invoices | ap_account_id | accounts(id) | set null |
| Purchase | purchase_invoices | journal_entry_id | journal_entries(id) | set null |
| Purchase | purchase_invoice_lines | product_id | products(id) | cascade |
| Purchase | purchase_invoice_lines | variant_id | product_variants(id) | set null |
| Purchase | purchase_invoice_lines | uom_id | units_of_measure(id) | default |
| Purchase | purchase_invoice_lines | tax_group_id | tax_groups(id) | set null |
| Purchase | purchase_invoice_lines | account_id | accounts(id) | set null |
| Purchase | purchase_returns | supplier_id | suppliers(id) | cascade |
| Purchase | purchase_returns | journal_entry_id | journal_entries(id) | set null |
| Purchase | purchase_return_lines | product_id | products(id) | cascade |
| Purchase | purchase_return_lines | variant_id | product_variants(id) | set null |
| Purchase | purchase_return_lines | batch_id | batches(id) | set null |
| Purchase | purchase_return_lines | serial_id | serials(id) | set null |
| Purchase | purchase_return_lines | from_location_id | warehouse_locations(id) | cascade |
| Purchase | purchase_return_lines | uom_id | units_of_measure(id) | default |
| Sales | sales_orders | customer_id | customers(id) | cascade |
| Sales | sales_orders | org_unit_id | org_units(id) | set null |
| Sales | sales_orders | warehouse_id | warehouses(id) | cascade |
| Sales | sales_orders | price_list_id | price_lists(id) | set null |
| Sales | sales_orders | created_by | users(id) | default |
| Sales | sales_orders | approved_by | users(id) | set null |
| Sales | sales_order_lines | product_id | products(id) | cascade |
| Sales | sales_order_lines | variant_id | product_variants(id) | set null |
| Sales | sales_order_lines | uom_id | units_of_measure(id) | default |
| Sales | sales_order_lines | tax_group_id | tax_groups(id) | set null |
| Sales | sales_order_lines | income_account_id | accounts(id) | set null |
| Sales | sales_order_lines | batch_id | batches(id) | set null |
| Sales | sales_order_lines | serial_id | serials(id) | set null |
| Sales | shipments | customer_id | customers(id) | cascade |
| Sales | shipments | warehouse_id | warehouses(id) | cascade |
| Sales | shipment_lines | product_id | products(id) | cascade |
| Sales | shipment_lines | variant_id | product_variants(id) | set null |
| Sales | shipment_lines | batch_id | batches(id) | set null |
| Sales | shipment_lines | serial_id | serials(id) | set null |
| Sales | shipment_lines | from_location_id | warehouse_locations(id) | cascade |
| Sales | shipment_lines | uom_id | units_of_measure(id) | default |
| Sales | sales_invoices | customer_id | customers(id) | cascade |
| Sales | sales_invoices | ar_account_id | accounts(id) | set null |
| Sales | sales_invoices | journal_entry_id | journal_entries(id) | set null |
| Sales | sales_invoice_lines | product_id | products(id) | cascade |
| Sales | sales_invoice_lines | variant_id | product_variants(id) | set null |
| Sales | sales_invoice_lines | uom_id | units_of_measure(id) | default |
| Sales | sales_invoice_lines | tax_group_id | tax_groups(id) | set null |
| Sales | sales_invoice_lines | income_account_id | accounts(id) | set null |
| Sales | sales_returns | customer_id | customers(id) | cascade |
| Sales | sales_returns | journal_entry_id | journal_entries(id) | set null |
| Sales | sales_return_lines | product_id | products(id) | cascade |
| Sales | sales_return_lines | variant_id | product_variants(id) | set null |
| Sales | sales_return_lines | batch_id | batches(id) | set null |
| Sales | sales_return_lines | serial_id | serials(id) | set null |
| Sales | sales_return_lines | to_location_id | warehouse_locations(id) | cascade |
| Sales | sales_return_lines | uom_id | units_of_measure(id) | default |
| Finance | journal_entries | created_by | users(id) | default |
| Finance | journal_entries | posted_by | users(id) | set null |
| Product | products | tax_group_id | tax_groups(id) | set null |

## Notes

- The shared migration uses guarded addition (`Schema::hasTable` + `Schema::hasColumn`) to remain safe in partial-module environments.
- FK creation is wrapped in a `try/catch` to tolerate already-existing constraints in non-clean environments.
- This matrix should be updated whenever a cross-module FK is moved into or out of the deferred migration.
