# Repository Audit And Product Search Implementation (2026-04-29)

## Scope

- Full repository architecture review with focus on modules under app/Modules.
- Deep review of Product, Pricing, Inventory for module boundaries, interdependencies, and runtime data flow.
- Implementation of a reusable product search API for Buy/Sell/POS contexts.

## Module Architecture Matrix

| Module | Domain | Application | Infrastructure | Migrations |
|---|---:|---:|---:|---:|
| Audit | Yes | Yes | Yes | Yes |
| Auth | Yes | Yes | Yes | Yes |
| Configuration | Yes | Yes | Yes | Yes |
| Core | Yes | Yes | Yes | Yes |
| Customer | Yes | Yes | Yes | Yes |
| Employee | Yes | Yes | Yes | Yes |
| Finance | Yes | Yes | Yes | Yes |
| HR | Yes | Yes | Yes | Yes |
| Inventory | Yes | Yes | Yes | Yes |
| OrganizationUnit | Yes | Yes | Yes | Yes |
| Pricing | Yes | Yes | Yes | Yes |
| Product | Yes | Yes | Yes | Yes |
| Purchase | Yes | Yes | Yes | Yes |
| Sales | Yes | Yes | Yes | Yes |
| Shared | No | No | Yes | No |
| Supplier | Yes | Yes | Yes | Yes |
| Tax | Yes | Yes | Yes | Yes |
| Tenant | Yes | Yes | Yes | Yes |
| User | Yes | Yes | Yes | Yes |
| Warehouse | Yes | Yes | Yes | Yes |

## Confirmed Patterns

- Clean module layering is consistently applied: Domain -> Application -> Infrastructure.
- Provider-driven dependency registration is used throughout modules.
- Tenant scoping is enforced via resolve.tenant middleware and tenant_id filtering in repositories.
- Product/Pricing/Inventory APIs are protected by auth:api + resolve.tenant.
- Pricing resolution is centralized in Pricing ResolvePriceService (price list + item matching).
- Inventory mutation logic is centralized in RecordStockMovementService + inventory repository.

## Interdependency Map (Product/Pricing/Inventory)

- Product provides: product master, variants, identifiers, UOM, attribute model, combo relationships.
- Pricing provides: price lists, price list items, customer/supplier assignments, price resolution endpoint.
- Inventory provides: movements, stock levels, reservations, valuation, transfer and cycle count workflows.
- Runtime flow:
  - Purchase/Sales events feed inventory listeners (goods receipt, shipment, returns).
  - Inventory stock levels provide quantity signals for selling and replenishment decisions.
  - Pricing price-list logic provides contextual prices for buy/sell scenarios.

## Gaps Found

1. Missing unified product search endpoint for buy/sell/POS that combines product + identifier + stock + price context in one payload.
2. Inventory stock update path had a race window due to read-modify-write behavior without explicit row locking at stock-level read.
3. Inventory stock movement workflow was not wrapped in a single DB transaction, increasing inconsistency risk under high write concurrency.
4. Search across batch/lot and variant attributes was not exposed as a single API contract.

## Implemented Changes

### 1) New Product Catalog Search API

- Added route: GET /api/products/search
- Middleware: auth:api, resolve.tenant
- New request validator: SearchProductCatalogRequest
- New controller: ProductSearchController
- New contract/service:
  - SearchProductCatalogServiceInterface
  - SearchProductCatalogService
- Provider binding added in ProductServiceProvider.

### 2) Search Features

- Multi-identifier search via q over:
  - product name / sku
  - variant name / sku
  - product_identifiers.value (barcode, QR, RFID, and other technologies)
  - batch_number / lot_number
  - variant attribute name/value
- Dynamic filters:
  - category_id
  - brand_id
  - warehouse_id
  - stock_status (all, in_stock, out_of_stock)
  - pricing_type (purchase, sales)
  - currency_id
  - quantity
  - customer_id
  - supplier_id
  - include_inactive
  - variant_attribute
- Response payload includes:
  - product and variant summary
  - identifiers
  - variant attributes
  - UOM (id, name, symbol)
  - pricing context + fallback price
  - quantity (on_hand, reserved, available)
  - combo relationships (is_combo, component_count, used_in_combo_count)

### 3) Concurrency/Reliability Hardening

- Wrapped RecordStockMovementService.execute in DB::transaction.
- Added lockForUpdate() in stock-level row fetch during stock delta application.

## Tests Added/Updated

- Updated ProductRoutesTest:
  - /api/products/search requires authentication.
  - route middleware contract asserted.
- Added ProductSearchEndpointsAuthenticatedTest:
  - validates authenticated search endpoint behavior and payload shape.
- Added ProductSearchPricingParityIntegrationTest:
  - validates assigned-customer precedence over default list.
  - validates quantity-aware min_quantity tier selection.
  - validates variant rows fall back to generic product price when variant-specific price is unavailable.
  - validates supplier-assigned purchase list precedence with quantity-tier pricing.
  - validates purchase variant rows fall back to generic product pricing when variant-specific price is unavailable.

## Recommendations (Next Iteration)

1. Completed in this pass: dedicated composite indexes for frequent search paths:
  - product_identifiers(tenant_id, is_active, value)
  - batches(tenant_id, product_id, variant_id, batch_number) and batches(tenant_id, product_id, variant_id, lot_number)
  - stock_levels(tenant_id, product_id, variant_id, location_id)
  - price_lists(tenant_id, type, currency_id, is_active)
  - price_list_items(tenant_id, product_id, variant_id, price_list_id)
2. Consider a read-optimized projection table/materialized view for POS-grade query latency at scale.
3. Add cache layer for hot lookups (tenant + warehouse + search term) with short TTL.
4. Completed in this pass: customer_id/supplier_id filters added so search honors assignment-or-default pricing context.
5. Add integration tests with mixed product/variant/batch/identifier datasets and concurrent stock update simulation.
6. Completed in this pass: context pricing now uses deterministic item-level winner selection (assignment/default + priority + specificity + min-quantity ordering) instead of aggregate MIN pricing.

## Outcome

The repository remains modular and maintainable with clear boundaries. The new product search capability closes the core operational gap for Buy/Sell/POS workflows while keeping implementation simple and extensible. Concurrency safety in inventory write paths is improved with transactional boundaries and row-level locking.
