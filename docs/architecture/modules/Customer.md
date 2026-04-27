# Module Contract: Customer

## 1. Bounded Context
- Purpose: Customer master data, addresses, contacts, and finance linkage points.
- Core business capabilities: Customer domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: Customer, CustomerAddress, CustomerContact
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: customer_addresses, customer_contacts, customers
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

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: customers
- Action endpoints: customers/{customer}/addresses, customers/{customer}/addresses/{address}, customers/{customer}/contacts, customers/{customer}/contacts/{contact}
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
- Current module-aligned tests: CustomerAddressServiceTest.php, CustomerContactServiceTest.php, CustomerEndpointsAuthenticatedTest.php, CustomerEntityTest.php, CustomerNestedRepositoryIntegrationTest.php, CustomerRoutesTest.php, CustomerServiceTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Address/contact integrity can drift without stronger uniqueness and lifecycle policies per customer context.
- Technical debt: Nested write flows rely on service conventions more than explicit domain invariants for contact/address cardinality.
- Planned refactors: Add invariants and tests for primary-contact/address rules and customer merge/deactivation workflows.
## 11. Concrete Source Map
- Module root: [app/Modules/Customer](app/Modules/Customer)
- Route source: [app/Modules/Customer/routes/api.php](app/Modules/Customer/routes/api.php)
- Provider files:
  - [app/Modules/Customer/Infrastructure/Providers/CustomerServiceProvider.php](app/Modules/Customer/Infrastructure/Providers/CustomerServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Customer/Domain/Entities/Customer.php](app/Modules/Customer/Domain/Entities/Customer.php)
  - [app/Modules/Customer/Domain/Entities/CustomerAddress.php](app/Modules/Customer/Domain/Entities/CustomerAddress.php)
  - [app/Modules/Customer/Domain/Entities/CustomerContact.php](app/Modules/Customer/Domain/Entities/CustomerContact.php)
- Application services (representative):
  - [app/Modules/Customer/Application/Services/CreateCustomerAddressService.php](app/Modules/Customer/Application/Services/CreateCustomerAddressService.php)
  - [app/Modules/Customer/Application/Services/CreateCustomerContactService.php](app/Modules/Customer/Application/Services/CreateCustomerContactService.php)
  - [app/Modules/Customer/Application/Services/CreateCustomerService.php](app/Modules/Customer/Application/Services/CreateCustomerService.php)
  - [app/Modules/Customer/Application/Services/DeleteCustomerAddressService.php](app/Modules/Customer/Application/Services/DeleteCustomerAddressService.php)
  - [app/Modules/Customer/Application/Services/DeleteCustomerContactService.php](app/Modules/Customer/Application/Services/DeleteCustomerContactService.php)
- Repository implementations (representative):
  - [app/Modules/Customer/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerAddressRepository.php](app/Modules/Customer/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerAddressRepository.php)
  - [app/Modules/Customer/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerContactRepository.php](app/Modules/Customer/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerContactRepository.php)
  - [app/Modules/Customer/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerRepository.php](app/Modules/Customer/Infrastructure/Persistence/Eloquent/Repositories/EloquentCustomerRepository.php)
- Migration files (representative):
  - [app/Modules/Customer/database/migrations/2024_01_01_400001_create_customers_table.php](app/Modules/Customer/database/migrations/2024_01_01_400001_create_customers_table.php)
  - [app/Modules/Customer/database/migrations/2024_01_01_400002_create_customer_addresses_table.php](app/Modules/Customer/database/migrations/2024_01_01_400002_create_customer_addresses_table.php)
  - [app/Modules/Customer/database/migrations/2024_01_01_400003_create_customer_contacts_table.php](app/Modules/Customer/database/migrations/2024_01_01_400003_create_customer_contacts_table.php)
- Test references:
  - [tests/Unit/Customer/CustomerAddressServiceTest.php](tests/Unit/Customer/CustomerAddressServiceTest.php)
  - [tests/Unit/Customer/CustomerContactServiceTest.php](tests/Unit/Customer/CustomerContactServiceTest.php)
  - [tests/Feature/CustomerEndpointsAuthenticatedTest.php](tests/Feature/CustomerEndpointsAuthenticatedTest.php)
  - [tests/Unit/Customer/CustomerEntityTest.php](tests/Unit/Customer/CustomerEntityTest.php)
  - [tests/Feature/CustomerNestedRepositoryIntegrationTest.php](tests/Feature/CustomerNestedRepositoryIntegrationTest.php)
  - [tests/Feature/CustomerRoutesTest.php](tests/Feature/CustomerRoutesTest.php)
  - [tests/Unit/Customer/CustomerServiceTest.php](tests/Unit/Customer/CustomerServiceTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.



