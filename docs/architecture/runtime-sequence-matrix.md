# Runtime Sequence Matrix

This document defines the canonical cross-module runtime flows for KVAutoERP.
It is intentionally concise and aligned with currently implemented route actions, domain events, listeners, and module boundaries.

## 1. Procure-to-Pay (P2P)

Goal: Move from supplier purchasing to inventory recognition and finance posting.

Primary modules:
- Purchase
- Inventory
- Finance
- Tax
- Supplier
- Product
- Tenant

Sequence:
1. Create purchase order in Purchase.
2. Confirm PO via route action `purchase-orders/{purchaseOrder}/confirm`.
3. Emit Purchase domain event `PurchaseOrderConfirmed`.
4. Receive goods via GRN and post via `grns/{grn}/post`.
5. Emit `GoodsReceiptPosted`; Inventory listener updates stock and valuation layers.
6. Create and approve purchase invoice via `purchase-invoices/{invoice}/approve`.
7. Emit `PurchaseInvoiceApproved`; Finance listener creates AP transaction and journal entries.
8. Record payment via `purchase-invoices/{invoice}/payment`.
9. Emit `PurchasePaymentRecorded`; Finance listener updates payment, AP reconciliation, and cash/bank entries.
10. Tax module resolves and stores transaction taxes for invoice and payment references when applicable.

Data contracts and integration points:
- Purchase to Inventory: goods receipt lines, product/variant/UOM, warehouse/location, quantities.
- Purchase to Finance: supplier, invoice number, currency, amount, due date, tax totals.
- Finance to Tax: taxable reference type/id and tax rule resolution context.

Constraints:
- Tenant isolation through `tenant_id` in all transactional tables.
- Monetary fields use DECIMAL precision to avoid float drift.
- Posting operations are idempotent where keys are provided.

## 2. Order-to-Cash (O2C)

Goal: Move from customer sales order to fulfillment, invoicing, and cash collection.

Primary modules:
- Sales
- Inventory
- Finance
- Tax
- Customer
- Product
- Pricing
- Tenant

Sequence:
1. Create sales order in Sales.
2. Confirm order via `sales-orders/{salesOrder}/confirm`.
3. Emit `SalesOrderConfirmed`.
4. Process shipment via `shipments/{shipment}/process`.
5. Emit `ShipmentProcessed`; Inventory listener decrements stock and writes stock movement/cost impact.
6. Post invoice via `sales-invoices/{salesInvoice}/post`.
7. Emit `SalesInvoicePosted`; Finance listener creates AR transaction and journal entries.
8. Record customer payment via `sales-invoices/{salesInvoice}/record-payment`.
9. Emit `SalesPaymentRecorded`; Finance listener posts payment and AR reconciliation.
10. Tax module resolves and records output taxes for invoice references.

Data contracts and integration points:
- Sales to Inventory: shipment line allocations and valuation references.
- Sales to Finance: customer, invoice totals, currency, receivable account references.
- Pricing to Sales: effective price list and discount resolution prior to invoice posting.

Constraints:
- Shipment and invoice posting must preserve ordering guarantees per tenant/document.
- AR balances are derived from immutable transaction history, not ad-hoc updates.

## 3. Inventory Movements and Valuation

Goal: Keep quantity and cost valuation consistent for all stock-affecting operations.

Primary modules:
- Inventory
- Warehouse
- Product
- Purchase
- Sales
- Finance

Movement sources:
- GRN posting (inbound)
- Shipment processing (outbound)
- Stock transfer and transfer order execution
- Stock adjustment and cycle count completion
- Returns (purchase and sales)

Canonical movement pipeline:
1. Business transaction emits a domain event.
2. Inventory listener validates tenant, product, warehouse, and location boundaries.
3. Stock movement record is written.
4. Stock levels are updated using row-version/concurrency checks.
5. Cost layers are adjusted for valuation strategy.
6. Trace log is written for reconciliation diagnostics.
7. Finance listener posts accounting effects for value-changing movements where required.

Consistency guardrails:
- No negative stock unless explicitly allowed by strategy.
- Every movement is traceable to a source reference (type/id).
- Valuation updates are transactional with movement writes.

## 4. HR to Finance Posting

Goal: Convert approved payroll and HR financial events into accounting entries.

Primary modules:
- HR
- Finance
- Employee
- OrganizationUnit
- Tenant

Sequence:
1. Payroll run is created and processed in HR.
2. Payslips are generated and event `PayslipGenerated` is emitted.
3. Payroll run is approved; event `PayrollRunApproved` is emitted.
4. Finance listener consumes payroll approval event.
5. Finance creates journal entries to record payroll expense, liabilities, and payable balances.
6. Optional payment execution follows Finance payment workflows for settlement.

Data contracts and integration points:
- HR to Finance: tenant_id, payroll run id, period dates, gross/deductions/net totals.
- Employee and OrganizationUnit context can be used for cost center/account mapping.

Constraints:
- Payroll posting is exactly-once per approved payroll run.
- Journal entries remain balanced and auditable with source references.

## Operating Notes

- Multi-tenancy is mandatory across all flows; no cross-tenant joins or updates.
- Controllers remain orchestration-thin; domain/application services own business rules.
- Event/listener boundaries are the preferred integration mechanism for cross-module runtime coupling.
- This matrix is the baseline for architecture tests and should be updated with new flow-level events and routes.