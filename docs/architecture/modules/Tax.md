# Module Contract: Tax

## 1. Bounded Context
- Purpose: Tax bounded context.
- Core business capabilities: Tax domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: TaxGroup, TaxRate, TaxRule, TransactionTax
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: tax_groups, tax_rates, tax_rules, transaction_taxes
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
- Action endpoints: tax/groups, tax/groups/{taxGroup}, tax/groups/{taxGroup}/rates, tax/groups/{taxGroup}/rates/{taxRate}, tax/groups/{taxGroup}/rules, tax/groups/{taxGroup}/rules/{taxRule}, tax/resolve, tax/transactions/{referenceType}/{referenceId}/lines
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
- Current module-aligned tests: TaxResolveServiceIntegrationTest.php, TaxRoutesTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Tax resolution correctness is sensitive to rule precedence and effective-date overlap conditions.
- Technical debt: Jurisdiction/rule composition strategy is service-centric without a formalized decision trace model.
- Planned refactors: Add deterministic rule-evaluation contracts and regression tests for edge precedence/date windows.
## 11. Concrete Source Map
- Module root: [app/Modules/Tax](app/Modules/Tax)
- Route source: [app/Modules/Tax/routes/api.php](app/Modules/Tax/routes/api.php)
- Provider files:
  - [app/Modules/Tax/Infrastructure/Providers/TaxServiceProvider.php](app/Modules/Tax/Infrastructure/Providers/TaxServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Tax/Domain/Entities/TaxGroup.php](app/Modules/Tax/Domain/Entities/TaxGroup.php)
  - [app/Modules/Tax/Domain/Entities/TaxRate.php](app/Modules/Tax/Domain/Entities/TaxRate.php)
  - [app/Modules/Tax/Domain/Entities/TaxRule.php](app/Modules/Tax/Domain/Entities/TaxRule.php)
  - [app/Modules/Tax/Domain/Entities/TransactionTax.php](app/Modules/Tax/Domain/Entities/TransactionTax.php)
- Application services (representative):
  - [app/Modules/Tax/Application/Services/CreateTaxGroupService.php](app/Modules/Tax/Application/Services/CreateTaxGroupService.php)
  - [app/Modules/Tax/Application/Services/CreateTaxRateService.php](app/Modules/Tax/Application/Services/CreateTaxRateService.php)
  - [app/Modules/Tax/Application/Services/CreateTaxRuleService.php](app/Modules/Tax/Application/Services/CreateTaxRuleService.php)
  - [app/Modules/Tax/Application/Services/DeleteTaxGroupService.php](app/Modules/Tax/Application/Services/DeleteTaxGroupService.php)
  - [app/Modules/Tax/Application/Services/DeleteTaxRateService.php](app/Modules/Tax/Application/Services/DeleteTaxRateService.php)
- Repository implementations (representative):
  - [app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaxGroupRepository.php](app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaxGroupRepository.php)
  - [app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaxRateRepository.php](app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaxRateRepository.php)
  - [app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaxRuleRepository.php](app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaxRuleRepository.php)
  - [app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTransactionTaxRepository.php](app/Modules/Tax/Infrastructure/Persistence/Eloquent/Repositories/EloquentTransactionTaxRepository.php)
- Migration files (representative):
  - [app/Modules/Tax/database/migrations/2024_01_01_750001a_create_tax_groups_table.php](app/Modules/Tax/database/migrations/2024_01_01_750001a_create_tax_groups_table.php)
  - [app/Modules/Tax/database/migrations/2024_01_01_750001b_create_tax_rates_table.php](app/Modules/Tax/database/migrations/2024_01_01_750001b_create_tax_rates_table.php)
  - [app/Modules/Tax/database/migrations/2024_01_01_750001c_create_tax_rules_table.php](app/Modules/Tax/database/migrations/2024_01_01_750001c_create_tax_rules_table.php)
  - [app/Modules/Tax/database/migrations/2024_01_01_750002_create_transaction_taxes_table.php](app/Modules/Tax/database/migrations/2024_01_01_750002_create_transaction_taxes_table.php)
- Test references:
  - [tests/Feature/TaxResolveServiceIntegrationTest.php](tests/Feature/TaxResolveServiceIntegrationTest.php)
  - [tests/Feature/TaxRoutesTest.php](tests/Feature/TaxRoutesTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.



