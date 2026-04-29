# Module Contract: Pricing

## 1. Bounded Context
- Purpose: Price-list policies for customer and supplier commercial pricing.
- Core business capabilities: Pricing domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: CustomerPriceList, PriceList, PriceListItem, SupplierPriceList
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: customer_price_lists, price_list_items, price_lists, supplier_price_lists
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
  - Contextual pricing consumed by Product catalog search via joins on price_lists and price_list_items.

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: No apiResource routes declared.
- Action endpoints: pricing/customers/{customer}/price-lists, pricing/customers/{customer}/price-lists/{assignment}, pricing/price-lists, pricing/price-lists/{priceList}, pricing/price-lists/{priceList}/items, pricing/price-lists/{priceList}/items/{priceListItem}, pricing/resolve, pricing/suppliers/{supplier}/price-lists, pricing/suppliers/{supplier}/price-lists/{assignment}
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
- Current module-aligned tests: PricingEndpointsAuthenticatedTest.php, PricingResolveServiceIntegrationTest.php, PricingRoutesTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Price resolution precedence and overlap handling can produce ambiguous outcomes in multi-list scenarios.
- Technical debt: Temporal validity and conflict resolution rules are implicit in service logic rather than a formal policy model.
- Planned refactors: Define deterministic price-precedence contracts and add conflict/overlap regression suites.
## 11. Concrete Source Map
- Module root: [app/Modules/Pricing](app/Modules/Pricing)
- Route source: [app/Modules/Pricing/routes/api.php](app/Modules/Pricing/routes/api.php)
- Provider files:
  - [app/Modules/Pricing/Infrastructure/Providers/PricingServiceProvider.php](app/Modules/Pricing/Infrastructure/Providers/PricingServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Pricing/Domain/Entities/CustomerPriceList.php](app/Modules/Pricing/Domain/Entities/CustomerPriceList.php)
  - [app/Modules/Pricing/Domain/Entities/PriceList.php](app/Modules/Pricing/Domain/Entities/PriceList.php)
  - [app/Modules/Pricing/Domain/Entities/PriceListItem.php](app/Modules/Pricing/Domain/Entities/PriceListItem.php)
  - [app/Modules/Pricing/Domain/Entities/SupplierPriceList.php](app/Modules/Pricing/Domain/Entities/SupplierPriceList.php)
- Application services (representative):
  - [app/Modules/Pricing/Application/Services/CreateCustomerPriceListService.php](app/Modules/Pricing/Application/Services/CreateCustomerPriceListService.php)
  - [app/Modules/Pricing/Application/Services/CreatePriceListItemService.php](app/Modules/Pricing/Application/Services/CreatePriceListItemService.php)
  - [app/Modules/Pricing/Application/Services/CreatePriceListService.php](app/Modules/Pricing/Application/Services/CreatePriceListService.php)
  - [app/Modules/Pricing/Application/Services/CreateSupplierPriceListService.php](app/Modules/Pricing/Application/Services/CreateSupplierPriceListService.php)
  - [app/Modules/Pricing/Application/Services/DeleteCustomerPriceListService.php](app/Modules/Pricing/Application/Services/DeleteCustomerPriceListService.php)
- Repository implementations (representative):
  - [app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerPriceListRepository.php](app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerPriceListRepository.php)
  - [app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentPriceListItemRepository.php](app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentPriceListItemRepository.php)
  - [app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentPriceListRepository.php](app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentPriceListRepository.php)
  - [app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentSupplierPriceListRepository.php](app/Modules/Pricing/Infrastructure/Persistence/Eloquent/Repositories/EloquentSupplierPriceListRepository.php)
- Migration files (representative):
  - [app/Modules/Pricing/database/migrations/2024_01_01_700001_create_price_lists_table.php](app/Modules/Pricing/database/migrations/2024_01_01_700001_create_price_lists_table.php)
  - [app/Modules/Pricing/database/migrations/2024_01_01_700002_create_price_list_items_table.php](app/Modules/Pricing/database/migrations/2024_01_01_700002_create_price_list_items_table.php)
  - [app/Modules/Pricing/database/migrations/2024_01_01_700003_create_customer_price_lists_table.php](app/Modules/Pricing/database/migrations/2024_01_01_700003_create_customer_price_lists_table.php)
  - [app/Modules/Pricing/database/migrations/2024_01_01_700004_create_supplier_price_lists_table.php](app/Modules/Pricing/database/migrations/2024_01_01_700004_create_supplier_price_lists_table.php)
- Test references:
  - [tests/Feature/PricingEndpointsAuthenticatedTest.php](tests/Feature/PricingEndpointsAuthenticatedTest.php)
  - [tests/Feature/PricingResolveServiceIntegrationTest.php](tests/Feature/PricingResolveServiceIntegrationTest.php)
  - [tests/Feature/PricingRoutesTest.php](tests/Feature/PricingRoutesTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.



