# Module Contract: Tenant

## 1. Bounded Context
- Purpose: Tenant lifecycle, plans, domains, and tenant-scoped configuration.
- Core business capabilities: Tenant domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: Tenant, TenantAttachment, TenantDomain, TenantPlan, TenantSetting
- Value objects: ApiKeys, CacheConfig, DatabaseConfig, FeatureFlags, MailConfig, QueueConfig
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: tenant_attachments, tenant_domains, tenant_plans, tenant_settings, tenants
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: TenantConfigChanged, TenantCreated, TenantDeleted, TenantDomainCreated, TenantDomainDeleted, TenantDomainUpdated, TenantPlanCreated, TenantPlanDeleted, TenantPlanUpdated, TenantSettingCreated, TenantSettingDeleted, TenantSettingUpdated, TenantUpdated
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: tenants
- Action endpoints: config/domain/{domain}, storage/tenant-attachments/{uuid}, tenant-plans, tenant-plans/{plan}, tenants/{tenant}/attachments, tenants/{tenant}/attachments/{attachment}, tenants/{tenant}/attachments/bulk, tenants/{tenant}/config, tenants/{tenant}/domains, tenants/{tenant}/domains/{domain}, tenants/{tenant}/settings, tenants/{tenant}/settings/{key}
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
- Current module-aligned tests: TenantDomainRoutesTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Tenant config/domain/plan mutations can create platform-wide impact if consistency checks are incomplete.
- Technical debt: Tenant configuration schema evolution lacks a strict compatibility matrix across modules.
- Planned refactors: Introduce versioned tenant-config contracts and compatibility checks in module bootstrap tests.
## 11. Concrete Source Map
- Module root: [app/Modules/Tenant](app/Modules/Tenant)
- Route source: [app/Modules/Tenant/routes/api.php](app/Modules/Tenant/routes/api.php)
- Provider files:
  - [app/Modules/Tenant/Infrastructure/Providers/TenantConfigServiceProvider.php](app/Modules/Tenant/Infrastructure/Providers/TenantConfigServiceProvider.php)
  - [app/Modules/Tenant/Infrastructure/Providers/TenantServiceProvider.php](app/Modules/Tenant/Infrastructure/Providers/TenantServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Tenant/Domain/Entities/Tenant.php](app/Modules/Tenant/Domain/Entities/Tenant.php)
  - [app/Modules/Tenant/Domain/Entities/TenantAttachment.php](app/Modules/Tenant/Domain/Entities/TenantAttachment.php)
  - [app/Modules/Tenant/Domain/Entities/TenantDomain.php](app/Modules/Tenant/Domain/Entities/TenantDomain.php)
  - [app/Modules/Tenant/Domain/Entities/TenantPlan.php](app/Modules/Tenant/Domain/Entities/TenantPlan.php)
  - [app/Modules/Tenant/Domain/Entities/TenantSetting.php](app/Modules/Tenant/Domain/Entities/TenantSetting.php)
- Application services (representative):
  - [app/Modules/Tenant/Application/Services/BulkUploadTenantAttachmentsService.php](app/Modules/Tenant/Application/Services/BulkUploadTenantAttachmentsService.php)
  - [app/Modules/Tenant/Application/Services/CreateTenantDomainService.php](app/Modules/Tenant/Application/Services/CreateTenantDomainService.php)
  - [app/Modules/Tenant/Application/Services/CreateTenantPlanService.php](app/Modules/Tenant/Application/Services/CreateTenantPlanService.php)
  - [app/Modules/Tenant/Application/Services/CreateTenantService.php](app/Modules/Tenant/Application/Services/CreateTenantService.php)
  - [app/Modules/Tenant/Application/Services/CreateTenantSettingService.php](app/Modules/Tenant/Application/Services/CreateTenantSettingService.php)
- Repository implementations (representative):
  - [app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantAttachmentRepository.php](app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantAttachmentRepository.php)
  - [app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantDomainRepository.php](app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantDomainRepository.php)
  - [app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantPlanRepository.php](app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantPlanRepository.php)
  - [app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantRepository.php](app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantRepository.php)
  - [app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantSettingRepository.php](app/Modules/Tenant/Infrastructure/Persistence/Eloquent/Repositories/EloquentTenantSettingRepository.php)
- Migration files (representative):
  - [app/Modules/Tenant/database/migrations/2024_01_01_000001_create_tenant_plans_table.php](app/Modules/Tenant/database/migrations/2024_01_01_000001_create_tenant_plans_table.php)
  - [app/Modules/Tenant/database/migrations/2024_01_01_000002_create_tenants_table.php](app/Modules/Tenant/database/migrations/2024_01_01_000002_create_tenants_table.php)
  - [app/Modules/Tenant/database/migrations/2024_01_01_000003_create_tenant_attachments_table.php](app/Modules/Tenant/database/migrations/2024_01_01_000003_create_tenant_attachments_table.php)
  - [app/Modules/Tenant/database/migrations/2024_01_01_000004_create_tenant_settings_table.php](app/Modules/Tenant/database/migrations/2024_01_01_000004_create_tenant_settings_table.php)
  - [app/Modules/Tenant/database/migrations/2024_01_01_000005_create_tenant_domains_table.php](app/Modules/Tenant/database/migrations/2024_01_01_000005_create_tenant_domains_table.php)
- Test references:
  - [tests/Feature/TenantDomainRoutesTest.php](tests/Feature/TenantDomainRoutesTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/Tenant/Domain/Events/TenantConfigChanged.php](app/Modules/Tenant/Domain/Events/TenantConfigChanged.php)
  - [app/Modules/Tenant/Domain/Events/TenantCreated.php](app/Modules/Tenant/Domain/Events/TenantCreated.php)
  - [app/Modules/Tenant/Domain/Events/TenantDeleted.php](app/Modules/Tenant/Domain/Events/TenantDeleted.php)
  - [app/Modules/Tenant/Domain/Events/TenantDomainCreated.php](app/Modules/Tenant/Domain/Events/TenantDomainCreated.php)



