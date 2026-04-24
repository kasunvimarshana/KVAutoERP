# KVAutoERP Architecture and Product Search Audit

Date: 2026-04-25
Scope: Full repository review with deep focus on app/Modules and end-to-end Product search read model.

## 1. Architecture Snapshot

### 1.1 Module topology
- Core: shared abstractions, base service/repository patterns, technical cross-cutting concerns.
- Tenant: tenant resolution and tenant-scoped configuration access.
- Auth/User: auth and identity boundaries.
- Product: product master data, variants, identifiers, UOM, attributes, search projection.
- Inventory: stock movement ledger and stock level state by location, batch, serial.
- Pricing: price lists, price list items, customer/supplier assignments, price resolution.
- Purchase/Sales: transaction documents and downstream inventory/price consumers.
- Finance/HR/OrganizationUnit/Audit/Configuration: adjacent bounded contexts.
- Shared: intentionally minimal shell.
- Stub or migration-led modules: Customer, Supplier, Pricing, Inventory, Purchase, Sales, Tax, Warehouse still contain uneven runtime depth across layers.

### 1.2 Design patterns in active use
- Clean Architecture layering per module: Domain, Application, Infrastructure.
- Repository pattern with interfaces in Domain and Eloquent implementations in Infrastructure.
- Thin controller pattern delegating to application services.
- Transaction orchestration in services and base service layer.
- Read-optimized projection pattern for product discovery (`product_search_projections`).

### 1.3 Boundary and dependency behavior
- Product projection repository reads from Product + Inventory + Pricing tables to build denormalized rows.
- Pricing resolution service remains authoritative for assignment-aware contextual pricing.
- Search service returns projection results and can optionally enrich each row with dynamic resolved pricing.
- Tenant isolation is preserved through explicit tenant_id filters and `resolve.tenant` middleware.

## 2. Data Flow Audit

### 2.1 Write flows
- Product, variant, identifier, and inventory stock movement writes trigger product-scoped projection refresh.
- Refresh path replaces all projection rows for affected product via force-delete then bulk insert, avoiding uniqueness collisions.

### 2.2 Read flows for Buy/Sell/POS
- Primary read path: `GET /api/products/search`.
- Projection supports fast lookup by text, SKU, identifiers, batch/lot, stock, category, brand.
- Optional dynamic pricing enrichment uses Pricing resolve service for assignment-aware price.

### 2.3 Real-time scenario coverage
- Covered now:
  - Product create/update/delete lifecycle reflection in projection.
  - Variant create/delete reflection in projection.
  - Identifier create reflection in projection.
  - Inventory quantity changes reflected through refresh hooks.
- Partially covered:
  - Price list changes are reflected on projection rebuild, but not yet event-driven refresh from Pricing writes.

## 3. Structural Findings

### 3.1 Strengths
- Clear modular separation and explicit repository interfaces.
- Projection architecture is established and test-backed.
- Search routes are middleware-protected and authenticated.
- Integration tests verify automatic refresh behavior end-to-end.

### 3.2 Gaps and risks identified
- Pricing freshness gap:
  - Projection default price snapshot depends on rebuild cadence; Pricing writes do not yet publish refresh triggers.
- Query scalability gap:
  - Search currently uses `LIKE` + token matching; no dedicated search backend or advanced text index strategy.
- Warehouse filter complexity:
  - Warehouse-aware joins and grouping can become expensive at scale.
- Data normalization consistency:
  - Several module schemas use nullable tenant_id in assignment tables; uniqueness semantics with nulls vary by engine.
- Runtime depth asymmetry:
  - Some modules still migration-heavy with thinner runtime logic, limiting full end-to-end scenario parity.

## 4. Implemented Enhancements (This iteration)

### 4.1 Projection schema and model enrichment
Added denormalized fields to unify Product, UOM, and default Pricing context in a single query model:
- UOM display fields:
  - base_uom_name, base_uom_symbol
  - purchase_uom_name, purchase_uom_symbol
  - sales_uom_name, sales_uom_symbol
- Default pricing snapshot fields:
  - default_sales_unit_price, default_sales_currency_id, default_sales_price_uom_id
  - default_purchase_unit_price, default_purchase_currency_id, default_purchase_price_uom_id

### 4.2 Projection rebuild enrichment
Projection rebuild now:
- Resolves UOM metadata from units_of_measure.
- Resolves default active Sales/Purchase pricing from pricing tables with validity windows and discount application.
- Includes UOM text in searchable_text to improve discoverability.

### 4.3 Search filter expansion
Search request and query now support:
- Fuzzy toggle (`fuzzy`) using SOUNDEX-assisted matching.
- Variant attribute text filter (`variant_attribute`).
- UOM-aware filter (`uom_id`) across base/purchase/sales and price UOM context.
- Pricing-context filters:
  - `price_context` (sales|purchase)
  - `min_price`, `max_price`
  - `currency_id` applied against selected pricing context.

### 4.4 Added integration test
New feature test validates:
- Projection row includes UOM display data.
- Projection row includes computed default sales price snapshot.
- Search supports context-based price filtering + UOM filtering end-to-end.

## 5. Validation Summary

Focused tests executed:
- ProductSearchProjectionAutomaticRefreshIntegrationTest: PASS (6 tests)
- ProductSearchProjectionUnifiedQueryModelIntegrationTest: PASS (1 test)

No regressions were observed in the modified search-projection area.

## 6. Recommended Next Steps

1. Add Pricing write hooks to refresh affected product projections in near real-time.
2. Add projection refresh hooks for batch/lot writes and warehouse-location structural updates.
3. Introduce dedicated search backend abstraction (MySQL FULLTEXT / PostgreSQL tsvector / Elasticsearch) behind repository contract.
4. Add cache strategy for high-frequency query patterns (POS terminal lookups).
5. Add query plan benchmarks and synthetic load tests for warehouse + pricing filters.
6. Add policy-driven filter configuration (feature-flag per tenant/context) for extensibility without controller changes.
