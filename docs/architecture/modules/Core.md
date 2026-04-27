# Module Contract: Core

## 1. Bounded Context
- Purpose: Foundational abstractions, shared traits/contracts, and technical primitives.
- Core business capabilities: Core domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: No domain entities detected in current structure.
- Value objects: ValueObject
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
- Published events: BaseEvent, UserScopedEvent
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: No apiResource routes declared.
- Action endpoints: No custom action routes declared.
- Auth and middleware requirements: n/a

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
- Current module-aligned tests: No module-name-matching tests detected.

## 10. Open Risks and Refactor Backlog
- Current risks: Shared primitives can become an unintended dependency hub if module-specific logic leaks into core abstractions.
- Technical debt: Base abstractions are broad, making it difficult to reason about allowed extension points and invariants.
- Planned refactors: Narrow and document Core extension seams; enforce no-business-logic guardrails in architecture tests.
## 11. Concrete Source Map
- Module root: [app/Modules/Core](app/Modules/Core)
- Provider files:
  - [app/Modules/Core/Infrastructure/Providers/CoreServiceProvider.php](app/Modules/Core/Infrastructure/Providers/CoreServiceProvider.php)
- Application services (representative):
  - [app/Modules/Core/Application/Services/BaseService.php](app/Modules/Core/Application/Services/BaseService.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/Core/Domain/Events/BaseEvent.php](app/Modules/Core/Domain/Events/BaseEvent.php)
  - [app/Modules/Core/Domain/Events/UserScopedEvent.php](app/Modules/Core/Domain/Events/UserScopedEvent.php)



