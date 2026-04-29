# Module Contract: Product

## 1. Bounded Context
- Purpose: Catalog, variants, identifiers, attributes, and product master governance.
- Core business capabilities: Product domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: ComboItem, Product, ProductAttribute, ProductAttributeGroup, ProductAttributeValue, ProductBrand, ProductCategory, ProductIdentifier, ProductVariant, UnitOfMeasure, UomConversion, VariantAttribute
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: attribute_groups, attribute_values, attributes, combo_items, product_brands, product_categories, product_identifiers, product_variants, products, units_of_measure, uom_conversions, variant_attribute_values, variant_attributes
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: No explicit domain events detected.
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.
- Read-side integration points:
  - Pricing read model join via price_lists and price_list_items for contextual purchase/sales pricing.
  - Inventory read model join via stock_levels and warehouse_locations for on-hand/reserved/available quantities.

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: combo-items, product-attribute-groups, product-attributes, product-attribute-values, product-brands, product-categories, product-identifiers, products, product-variants, units-of-measure, uom-conversions, variant-attributes
- Action endpoints: products/search, uom-conversions/resolve
- Auth and middleware requirements: auth:api, resolve.tenant

## 7. Operational Profile
- High-volume query paths: Status/date/party scoped document retrieval and tenant-scoped listings.
- Required indexes: Composite tenant + business key/status/date indexes in module migrations.
- Expected concurrency hotspots: Approval/posting/stock-allocation style state transitions.
- Observability signals (logs/metrics/audit): Audit logs, domain events, and endpoint/test traces.

## 8. Security and Compliance
- Sensitive data classes: PII/financial/operational data based on module context.
- Access-control model: API middleware plus role/permission controls where applicable.
- Audit obligations: Changes should be traceable through audit/logging/event records.
- Data retention requirements: Domain and regulatory policy dependent.

## 9. Test Coverage Expectations
- Architecture guardrails: Boundary/provider/route/migration guardrail tests.
- Feature tests: Endpoint and integration flows for module routes/services.
- Integration tests: Repository and cross-module posting/allocation consistency checks.
- Regression scenarios: Status transitions, FK integrity, tenant isolation, and rounding/precision behavior.
- Current module-aligned tests: ProductBrandEndpointsAuthenticatedTest.php, ProductBrandRepositoryIntegrationTest.php, ProductBrandRoutesTest.php, ProductBrandServiceTest.php, ProductCatalogEndpointsAuthenticatedTest.php, ProductCatalogRepositoryIntegrationTest.php, ProductCatalogRoutesTest.php, ProductCategoryEndpointsAuthenticatedTest.php, ProductCategoryRepositoryIntegrationTest.php, ProductCategoryRoutesTest.php, ProductCategoryServiceTest.php, ProductEndpointsAuthenticatedTest.php, ProductEntityTest.php, ProductIdentifierEndpointsAuthenticatedTest.php, ProductIdentifierRepositoryIntegrationTest.php, ProductIdentifierRoutesTest.php, ProductIdentifierServiceTest.php, ProductModuleGuardrailsTest.php, ProductRepositoryIntegrationTest.php, ProductRoutesTest.php, ProductServiceTest.php, ProductUomConversionConsistencyTest.php, ProductVariantEndpointsAuthenticatedTest.php, ProductVariantRepositoryIntegrationTest.php, ProductVariantRoutesTest.php, ProductVariantServiceTest.php, SupplierProductServiceTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Variant/attribute combinatorics and identifier uniqueness can regress without strict invariants at scale.
- Technical debt: Catalog consistency rules across product, variant, identifier, and UOM conversion are spread across multiple services.
- Planned refactors: Consolidate catalog invariants and add high-volume mutation tests for variant and identifier integrity.
## 11. Concrete Source Map
- Module root: [app/Modules/Product](app/Modules/Product)
- Route source: [app/Modules/Product/routes/api.php](app/Modules/Product/routes/api.php)
- Provider files:
  - [app/Modules/Product/Infrastructure/Providers/ProductServiceProvider.php](app/Modules/Product/Infrastructure/Providers/ProductServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Product/Domain/Entities/ComboItem.php](app/Modules/Product/Domain/Entities/ComboItem.php)
  - [app/Modules/Product/Domain/Entities/Product.php](app/Modules/Product/Domain/Entities/Product.php)
  - [app/Modules/Product/Domain/Entities/ProductAttribute.php](app/Modules/Product/Domain/Entities/ProductAttribute.php)
  - [app/Modules/Product/Domain/Entities/ProductAttributeGroup.php](app/Modules/Product/Domain/Entities/ProductAttributeGroup.php)
  - [app/Modules/Product/Domain/Entities/ProductAttributeValue.php](app/Modules/Product/Domain/Entities/ProductAttributeValue.php)
- Application services (representative):
  - [app/Modules/Product/Application/Services/CreateComboItemService.php](app/Modules/Product/Application/Services/CreateComboItemService.php)
  - [app/Modules/Product/Application/Services/CreateProductAttributeGroupService.php](app/Modules/Product/Application/Services/CreateProductAttributeGroupService.php)
  - [app/Modules/Product/Application/Services/CreateProductAttributeService.php](app/Modules/Product/Application/Services/CreateProductAttributeService.php)
  - [app/Modules/Product/Application/Services/CreateProductAttributeValueService.php](app/Modules/Product/Application/Services/CreateProductAttributeValueService.php)
  - [app/Modules/Product/Application/Services/CreateProductBrandService.php](app/Modules/Product/Application/Services/CreateProductBrandService.php)
- Repository implementations (representative):
  - [app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentComboItemRepository.php](app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentComboItemRepository.php)
  - [app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductAttributeGroupRepository.php](app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductAttributeGroupRepository.php)
  - [app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductAttributeRepository.php](app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductAttributeRepository.php)
  - [app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductAttributeValueRepository.php](app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductAttributeValueRepository.php)
  - [app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductBrandRepository.php](app/Modules/Product/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductBrandRepository.php)
- Migration files (representative):
  - [app/Modules/Product/database/migrations/2024_01_01_600001_create_product_categories_table.php](app/Modules/Product/database/migrations/2024_01_01_600001_create_product_categories_table.php)
  - [app/Modules/Product/database/migrations/2024_01_01_600002_create_units_of_measure_table.php](app/Modules/Product/database/migrations/2024_01_01_600002_create_units_of_measure_table.php)
  - [app/Modules/Product/database/migrations/2024_01_01_600003_create_uom_conversions_table.php](app/Modules/Product/database/migrations/2024_01_01_600003_create_uom_conversions_table.php)
  - [app/Modules/Product/database/migrations/2024_01_01_600004a_create_product_brands_table.php](app/Modules/Product/database/migrations/2024_01_01_600004a_create_product_brands_table.php)
  - [app/Modules/Product/database/migrations/2024_01_01_600004b_create_attribute_groups_table.php](app/Modules/Product/database/migrations/2024_01_01_600004b_create_attribute_groups_table.php)
- Test references:
  - [tests/Feature/ProductBrandEndpointsAuthenticatedTest.php](tests/Feature/ProductBrandEndpointsAuthenticatedTest.php)
  - [tests/Feature/ProductBrandRepositoryIntegrationTest.php](tests/Feature/ProductBrandRepositoryIntegrationTest.php)
  - [tests/Feature/ProductBrandRoutesTest.php](tests/Feature/ProductBrandRoutesTest.php)
  - [tests/Unit/Product/ProductBrandServiceTest.php](tests/Unit/Product/ProductBrandServiceTest.php)
  - [tests/Feature/ProductCatalogEndpointsAuthenticatedTest.php](tests/Feature/ProductCatalogEndpointsAuthenticatedTest.php)
  - [tests/Feature/ProductCatalogRepositoryIntegrationTest.php](tests/Feature/ProductCatalogRepositoryIntegrationTest.php)
  - [tests/Feature/ProductCatalogRoutesTest.php](tests/Feature/ProductCatalogRoutesTest.php)
  - [tests/Feature/ProductCategoryEndpointsAuthenticatedTest.php](tests/Feature/ProductCategoryEndpointsAuthenticatedTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Cross-module query orchestration for Buy/Sell/POS search:
  - Endpoint: [app/Modules/Product/routes/api.php](app/Modules/Product/routes/api.php)
  - Controller: [app/Modules/Product/Infrastructure/Http/Controllers/ProductSearchController.php](app/Modules/Product/Infrastructure/Http/Controllers/ProductSearchController.php)
  - Service: [app/Modules/Product/Application/Services/SearchProductCatalogService.php](app/Modules/Product/Application/Services/SearchProductCatalogService.php)



