# Module Contract: Audit

## 1. Bounded Context
- Purpose: Cross-cutting audit trail capture and query support.
- Core business capabilities: Audit domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: AuditLog
- Value objects: AuditAction
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: audit_logs
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
- Resource endpoints: No apiResource routes declared.
- Action endpoints: audit-logs, audit-logs/{auditLogId}
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
- Current module-aligned tests: AuditEndpointsAuthenticatedTest.php, AuditLogControllerTest.php, AuditLogResourceTest.php, AuditRepositoryIntegrationTest.php, AuditRoutesTest.php, AuditServiceTest.php, AuditTimestampGuardrailsTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Cross-module audit event coverage is uneven, with potential blind spots in async/background flows.
- Technical debt: Payload schemas for audit metadata are loosely standardized, which can hinder reliable downstream analytics.
- Planned refactors: Define event-level audit contracts and add contract tests for high-value write paths.
## 11. Concrete Source Map
- Module root: [app/Modules/Audit](app/Modules/Audit)
- Route source: [app/Modules/Audit/routes/api.php](app/Modules/Audit/routes/api.php)
- Provider files:
  - [app/Modules/Audit/Infrastructure/Providers/AuditServiceProvider.php](app/Modules/Audit/Infrastructure/Providers/AuditServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Audit/Domain/Entities/AuditLog.php](app/Modules/Audit/Domain/Entities/AuditLog.php)
- Application services (representative):
  - [app/Modules/Audit/Application/Services/AuditService.php](app/Modules/Audit/Application/Services/AuditService.php)
- Repository implementations (representative):
  - [app/Modules/Audit/Infrastructure/Persistence/Eloquent/Repositories/EloquentAuditRepository.php](app/Modules/Audit/Infrastructure/Persistence/Eloquent/Repositories/EloquentAuditRepository.php)
- Migration files (representative):
  - [app/Modules/Audit/database/migrations/2024_01_01_130001_create_audit_logs_table.php](app/Modules/Audit/database/migrations/2024_01_01_130001_create_audit_logs_table.php)
- Test references:
  - [tests/Feature/AuditEndpointsAuthenticatedTest.php](tests/Feature/AuditEndpointsAuthenticatedTest.php)
  - [tests/Unit/Audit/AuditLogControllerTest.php](tests/Unit/Audit/AuditLogControllerTest.php)
  - [tests/Unit/Audit/AuditLogResourceTest.php](tests/Unit/Audit/AuditLogResourceTest.php)
  - [tests/Feature/AuditRepositoryIntegrationTest.php](tests/Feature/AuditRepositoryIntegrationTest.php)
  - [tests/Feature/AuditRoutesTest.php](tests/Feature/AuditRoutesTest.php)
  - [tests/Unit/Audit/AuditServiceTest.php](tests/Unit/Audit/AuditServiceTest.php)
  - [tests/Unit/Architecture/AuditTimestampGuardrailsTest.php](tests/Unit/Architecture/AuditTimestampGuardrailsTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.



