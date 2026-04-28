# Migration and Model Governance Checklist

This checklist standardizes migration and model quality across all modules.

## 1. Migration Integrity

- Every tenant-scoped table includes `tenant_id` with a foreign key to `tenants.id`.
- Cross-module references use explicit table names in `constrained('<table>', 'id', '<index_name>')`.
- Foreign key delete behavior is intentional (`cascadeOnDelete`, `nullOnDelete`, or `restrictOnDelete`) and documented in the migration.
- Every business identifier that must be unique per tenant has a composite unique index with `tenant_id`.
- For org-unit-aware tables, uniqueness is scoped with `tenant_id + org_unit_id + <business_key>` to support optional organizational isolation.
- High-frequency filtering paths include composite indexes (for example: `tenant_id + status + date`).
- Money fields use `decimal(20, 6)`.
- No duplicate foreign key declarations exist for the same column.

## 2. Model Consistency

- Every model defines `protected $table`.
- Enum/status fields have explicit casts (`string` or PHP backed enum class).
- Decimal and date columns have explicit casts.
- JSON columns are cast to `array`.
- `row_version` fields are cast to `integer`.
- Tenant-scoped models use `HasTenant`.
- Audited models use `HasAudit`.

## 3. Module Boundary Rules

- Domain layer does not import Infrastructure classes.
- Repository interfaces remain in Domain.
- Repository implementations remain in Infrastructure.
- Application services orchestrate transactions and domain rules, not controllers.
- Controllers remain thin and delegate to service contracts.

## 4. Cross-Module Contract Rules

- Integration points are event-driven where practical.
- Events crossing module boundaries are documented in module contracts.
- Runtime workflows that affect Finance produce balanced journal artifacts or are explicitly excluded and tested.
- Idempotency is enforced for external retry-sensitive flows.

## 5. Required Validation

Run from repository root:

```bash
composer install
./vendor/bin/phpunit --filter=ConfigurationModuleMigrationSmokeTest
./vendor/bin/phpunit --filter=Inventory
./vendor/bin/phpunit --filter=Purchase
./vendor/bin/phpunit --filter=Sales
./vendor/bin/pint
```

## 6. Audit Cadence

- Execute this checklist before each release.
- Update architecture documents after any schema or module-boundary change.
- Keep module contract docs in `docs/architecture/modules/` aligned with migration and event changes.
