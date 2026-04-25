# Product, Pricing, Inventory Architecture Audit (2026-04-25)

## Scope
- Full repository architecture review with deep focus on modules under `app/Modules`.
- Detailed implementation review for Product, Pricing, Inventory modules.
- Operational-readiness review for real-time Buy, Sell, and POS workflows.

## Current Architecture Summary
- Architecture style: modular monolith with Clean Architecture-inspired layering per module.
- Layering pattern generally consistent:
  - Domain: entities, value objects, repository interfaces
  - Application: contracts, DTOs, services
  - Infrastructure: Eloquent models/repositories, HTTP controllers/requests/resources, service providers
- Multi-tenancy consistently enforced through `resolve.tenant` middleware and tenant-aware queries.
- Product module is broad and mature in CRUD coverage; Inventory and Pricing are focused and event-aware.

## Module Boundaries and Dependencies
- Product owns catalog master data: products, variants, attributes, identifiers, UOM conversions, combo structures.
- Pricing owns transactional price decisioning: price lists, assignments, item-level match logic.
- Inventory owns stock movement and on-hand/reserved snapshots with warehouse and location dimensions.
- Cross-module runtime dependency patterns found:
  - Inventory -> Product for UOM normalization.
  - Inventory <- Purchase/Sales domain events.
  - Product search (new implementation) -> Pricing repository for contextual price resolution.

## Data Model and Relationship Review
- Product identifiers support barcode, QR, RFID and custom formats.
- Variants and attribute-value bridges support matrix-style SKU definitions.
- Inventory stock levels normalized by product, variant, location, batch, serial.
- Pricing resolution supports assignment priority, variant specificity, date windows, and quantity tiers.

## Key Gaps and Risks Found
1. Concurrency risk in inventory stock updates:
- `EloquentInventoryStockRepository::applyStockDelta` performs read-then-write without explicit row locking.
- Risk: lost update under high-concurrency movement ingestion.

2. Search capability gap prior to this change:
- Product listing endpoint only supported narrow field filters and lacked multi-identifier lookup.
- No integrated stock + pricing contextual response.

3. Incomplete indexing for heavy search paths:
- Existing indexes improve tenant/slug/name lookups but not all identifier and lot/serial lookup patterns.

4. Migration defect in inventory hardening migration:
- `2026_04_20_000001_harden_inventory_indexes_and_serial_fields.php` adds `manufacture_date` to `serials` using `after('lot_number')`.
- `serials` table has no `lot_number` column.

5. Data consistency concern in cross-context retrieval:
- Buy/Sell/POS require deterministic context-UOM and pricing type fallback behavior.
- This is now centralized in the new Product search service.

## Implemented Solution in This Change Set
### New Product Search System
- Added configurable, reusable catalog search architecture:
  - Contract: `SearchProductCatalogServiceInterface`
  - Service: `SearchProductCatalogService`
  - Repository boundary: `ProductSearchRepositoryInterface`
  - Infrastructure implementation: `EloquentProductSearchRepository`
  - HTTP endpoint and validation:
    - `GET /api/products/search`
    - `ProductSearchController`
    - `SearchProductCatalogRequest`
  - Config: `config/product_search.php`

### Search Capabilities Delivered
- Multi-identifier query support:
  - Product name
  - Product/variant SKU
  - Product identifiers (`product_identifiers.value`) including QR/RFID/barcode
  - Batch number, lot number
  - Serial number
  - Variant attribute values
- Dynamic filters:
  - category, brand, variant, product type
  - warehouse-specific stock
  - stock status (`in_stock`, `out_of_stock`, `low_stock`)
  - active/inactive inclusion toggle
- Workflow-aware pricing:
  - context mapping (`buy`, `sell`, `pos` -> pricing type)
  - currency + optional customer/supplier context
  - quantity-sensitive price list matching via existing pricing repository logic
- Rich response payload:
  - category/brand references
  - base/sales/purchase UOM blocks
  - stock quantities and unit cost snapshot
  - identifiers and variant attributes
  - combo-component relationships

## Real-Time Scenario Coverage
- POS scan flow: resolves product via QR/RFID/barcode with warehouse stock and sales price.
- Buy workflow: supplier context and purchase pricing with inventory-aware availability snapshots.
- Sell workflow: customer context and quantity-tier sales pricing.
- Low-stock monitoring: explicit threshold-based search filter for replenishment screens.

## Performance Notes
- Search query uses stock aggregation subquery to avoid per-row stock recalculation.
- Related payload sections (identifiers, variant attributes, combo links) are bulk-loaded per page.
- Remaining optimization candidates:
  - Add composite indexes for high-frequency term paths in `product_identifiers`, `batches`, `serials`.
  - Consider materialized searchable catalog projection for very high SKU volumes.

## Architecture Compliance Notes
- New search flow is module-contained in Product, with dependency on Pricing repository contract at application layer.
- Existing controller thinness and provider-based dependency registration conventions preserved.
- Tenant scoping remains explicit and mandatory in search input.

## Recommended Next Steps (Prioritized)
1. Add transactional row locking in inventory stock updates (`SELECT ... FOR UPDATE` style) to prevent race conditions.
2. Fix inventory migration defect (`after('lot_number')`) with safe, engine-aware column addition strategy.
3. Add targeted indexes for identifier and lot/serial search paths under tenant scope.
4. Introduce request-level idempotency key support for stock movement write APIs.
5. Add performance tests for search endpoint with realistic high-cardinality datasets.
6. Consider exposing a dedicated pricing context object contract for all UI clients.

## Files Added/Changed for Search Capability
- `app/Modules/Product/Application/Contracts/SearchProductCatalogServiceInterface.php`
- `app/Modules/Product/Application/Services/SearchProductCatalogService.php`
- `app/Modules/Product/Domain/RepositoryInterfaces/ProductSearchRepositoryInterface.php`
- `app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductSearchRepository.php`
- `app/Modules/Product/Infrastructure/Http/Controllers/ProductSearchController.php`
- `app/Modules/Product/Infrastructure/Http/Requests/SearchProductCatalogRequest.php`
- `app/Modules/Product/routes/api.php`
- `app/Modules/Product/Infrastructure/Providers/ProductServiceProvider.php`
- `config/product_search.php`
- `tests/Feature/ProductCatalogSearchIntegrationTest.php`
- `tests/Feature/ProductRoutesTest.php`
