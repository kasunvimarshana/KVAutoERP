# Org-Unit Isolation Audit and Refactor (2026-04-28)

## Scope

This audit reviewed the full modular schema surface under `app/Modules/*/database/migrations` and aligned unique-key governance with optional organizational unit isolation.

## Architectural Baseline

- Multi-tenancy is consistently enforced with `tenant_id` on module-owned business tables.
- Optional org-unit partitioning is broadly modeled with nullable `org_unit_id` foreign keys.
- Prior to this refactor, most unique constraints were tenant-only (`tenant_id + business_key`) even where `org_unit_id` existed.

## Refactor Objective

Standardize business-key uniqueness for org-unit-aware tables to the composite pattern:

- `tenant_id + org_unit_id + <business_key>`

This enables branch/department-level key reuse inside a tenant while preserving uniqueness within each organizational unit.

## Refactor Result

- Updated 53 existing migration files in place (no incremental migration chain added).
- Composite unique definitions were upgraded across Customer, Employee, Finance, HR, Inventory, Pricing, Product, Purchase, Sales, Supplier, User, and Warehouse modules where both:
  - the table already contains `org_unit_id`, and
  - the unique key previously started with `tenant_id`.
- Existing global/system uniques (for platform reference tables and UUID columns) were left unchanged.

## Data Integrity and Performance Notes

- Composite uniqueness now aligns better with module boundaries that already carry `org_unit_id`.
- Existing index coverage remains intact for read paths; only unique key scopes were adjusted.
- `valuation_configs` already used org-unit-aware composite uniqueness and remains consistent with this standard.

## Governance Update

`docs/architecture/migration-model-governance-checklist.md` now explicitly requires org-unit-aware unique scoping for org-unit-aware tables.

## Follow-Up Recommendations

1. Add schema tests to assert required composite unique index names and columns per critical module.
2. Validate command and sequence generation services (PO/SO/Invoice/etc.) against org-unit-specific numbering policy.
3. Run integration workflows for high-throughput modules (Sales, Purchase, Inventory, Finance) with mixed org-unit traffic to validate behavior under concurrent writes.
