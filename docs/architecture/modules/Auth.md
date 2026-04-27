# Module Contract: Auth

## 1. Bounded Context
- Purpose: Authentication flows and token-related route/service concerns.
- Core business capabilities: Auth domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: AccessToken
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: No module-owned runtime tables detected.
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: UserLoggedIn, UserLoggedOut, UserRegistered
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: auth
- Resource endpoints: No apiResource routes declared.
- Action endpoints: /forgot-password, /login, /logout, /me, /refresh, /register, /reset-password, /sso/{provider}
- Auth and middleware requirements: auth:api

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
- Current module-aligned tests: AuditEndpointsAuthenticatedTest.php, CustomerEndpointsAuthenticatedTest.php, EmployeeEndpointsAuthenticatedTest.php, FinanceFiscalEndpointsAuthenticatedTest.php, HREndpointsAuthenticatedTest.php, InventoryStockReservationEndpointsAuthenticatedTest.php, PricingEndpointsAuthenticatedTest.php, ProductBrandEndpointsAuthenticatedTest.php, ProductCatalogEndpointsAuthenticatedTest.php, ProductCategoryEndpointsAuthenticatedTest.php, ProductEndpointsAuthenticatedTest.php, ProductIdentifierEndpointsAuthenticatedTest.php, ProductVariantEndpointsAuthenticatedTest.php, UnitOfMeasureEndpointsAuthenticatedTest.php, UomConversionEndpointsAuthenticatedTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Token/session lifecycle edge cases (refresh, revocation, SSO callback failure) may not be uniformly hardened across providers.
- Technical debt: Authorization strategy composition (ABAC/RBAC paths) is spread across services without a single policy map artifact.
- Planned refactors: Publish a centralized auth policy matrix and add endpoint tests for denial/expiry/replay scenarios.
## 11. Concrete Source Map
- Module root: [app/Modules/Auth](app/Modules/Auth)
- Route source: [app/Modules/Auth/routes/api.php](app/Modules/Auth/routes/api.php)
- Provider files:
  - [app/Modules/Auth/Infrastructure/Providers/AuthModuleServiceProvider.php](app/Modules/Auth/Infrastructure/Providers/AuthModuleServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Auth/Domain/Entities/AccessToken.php](app/Modules/Auth/Domain/Entities/AccessToken.php)
- Application services (representative):
  - [app/Modules/Auth/Application/Services/AbacAuthorizationStrategy.php](app/Modules/Auth/Application/Services/AbacAuthorizationStrategy.php)
  - [app/Modules/Auth/Application/Services/AuthenticationService.php](app/Modules/Auth/Application/Services/AuthenticationService.php)
  - [app/Modules/Auth/Application/Services/AuthorizationService.php](app/Modules/Auth/Application/Services/AuthorizationService.php)
  - [app/Modules/Auth/Application/Services/ForgotPasswordService.php](app/Modules/Auth/Application/Services/ForgotPasswordService.php)
  - [app/Modules/Auth/Application/Services/LoginService.php](app/Modules/Auth/Application/Services/LoginService.php)
- Test references:
  - [tests/Feature/AuditEndpointsAuthenticatedTest.php](tests/Feature/AuditEndpointsAuthenticatedTest.php)
  - [tests/Feature/CustomerEndpointsAuthenticatedTest.php](tests/Feature/CustomerEndpointsAuthenticatedTest.php)
  - [tests/Feature/EmployeeEndpointsAuthenticatedTest.php](tests/Feature/EmployeeEndpointsAuthenticatedTest.php)
  - [tests/Feature/FinanceFiscalEndpointsAuthenticatedTest.php](tests/Feature/FinanceFiscalEndpointsAuthenticatedTest.php)
  - [tests/Feature/HREndpointsAuthenticatedTest.php](tests/Feature/HREndpointsAuthenticatedTest.php)
  - [tests/Feature/InventoryStockReservationEndpointsAuthenticatedTest.php](tests/Feature/InventoryStockReservationEndpointsAuthenticatedTest.php)
  - [tests/Feature/PricingEndpointsAuthenticatedTest.php](tests/Feature/PricingEndpointsAuthenticatedTest.php)
  - [tests/Feature/ProductBrandEndpointsAuthenticatedTest.php](tests/Feature/ProductBrandEndpointsAuthenticatedTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/Auth/Domain/Events/UserLoggedIn.php](app/Modules/Auth/Domain/Events/UserLoggedIn.php)
  - [app/Modules/Auth/Domain/Events/UserLoggedOut.php](app/Modules/Auth/Domain/Events/UserLoggedOut.php)
  - [app/Modules/Auth/Domain/Events/UserRegistered.php](app/Modules/Auth/Domain/Events/UserRegistered.php)



