# Module Knowledge Base

## Purpose

This document is the canonical architecture and module reference for KVAutoERP.
It standardizes module responsibilities, boundaries, interdependencies, and migration design rules.

## System Architecture

KVAutoERP follows a modular Clean Architecture on Laravel:

- Domain layer: entities, value objects, events, repository interfaces.
- Application layer: service contracts, services, DTOs.
- Infrastructure layer: Eloquent persistence, repositories, HTTP adapters, providers.

Each module must keep business logic in Domain/Application and keep controllers/repositories thin.

## Layering Contract

For every module under `app/Modules/<Module>`:

- Domain must never import Infrastructure namespaces.
- Application must never import Infrastructure namespaces.
- Infrastructure may depend on Domain and Application contracts.
- Cross-module access must happen via contracts, repositories, events, or explicit FK references.

## Module Inventory

### Foundation modules

- Core: shared abstractions only (base classes, events, contracts).
- Shared: thin integration shell (routes/infrastructure only).

### Business modules

- Tenant: tenant lifecycle, plans, domains, settings.
- User: users, roles, permissions, user attachments/devices.
- Auth: authentication/session/token-related concerns.
- Audit: audit logging and change traceability.
- Configuration: global reference data (countries, currencies, languages, timezones).
- OrganizationUnit: org hierarchy and user mapping.
- Employee: employee profile and user synchronization.
- HR: shifts, attendance, leave, payroll, performance.
- Customer: customer master, contacts, addresses.
- Supplier: supplier master, contacts, addresses, product mappings.
- Product: product catalog, categories, brands, attributes, variants, UOM/conversions.
- Pricing: customer/supplier price lists and resolution.
- Warehouse: warehouse and location hierarchy.
- Inventory: stock movement, reservations, transfers, cost layers, cycle counts, valuation config.
- Tax: tax groups, rates, rules, transaction tax records.
- Sales: order, shipment, invoice, return lifecycle.
- Purchase: PO, GRN, purchase invoice, purchase return lifecycle.
- Finance: chart of accounts, journals, periods, AR/AP, payments, bank ops, approvals.

## Cross-Module Dependency Rules

### Allowed and expected

- All tenant-owned modules use `tenant_id` and tenant resolution middleware.
- Product, Warehouse, Tax, Pricing, Finance are shared operational dependencies for Sales and Purchase.
- HR and Employee can depend on User for identity linkage.
- Finance consumes business events to post accounting effects.

### Not allowed

- Domain to Infrastructure imports.
- Circular module dependencies.
- Bypassing repository interfaces for core business persistence.

## Data Ownership and Scope

- Configuration module is global reference data and does not carry tenant scope.
- Most business modules are tenant-scoped and require explicit tenant filtering.
- Tenant deletion must cascade through tenant-owned tables unless explicit retention is required.

## Canonical End-to-End Flows

### Sales flow

1. Sales order created and priced.
2. Inventory reservations/allocations applied.
3. Shipment posted from warehouse locations.
4. Sales invoice posted.
5. Finance receives posting event and writes journal entries.
6. Returns/credit flow updates stock and finance.

### Purchase flow

1. Purchase order created against supplier and warehouse.
2. GRN captures receiving and quality disposition.
3. Purchase invoice posted.
4. Finance receives posting event and writes AP entries.
5. Purchase returns/debit note flow adjusts inventory and finance.

### HR/payroll flow

1. Attendance and leave accumulate.
2. Payroll run produces payslips.
3. Finance receives payroll posting events.

## Migration Governance (Initial Development Phase)

This repository is in an initial development phase. Migration history should remain clean and readable.

- Prefer modifying existing module migration files when correcting schema design.
- Avoid creating many incremental correction migrations for pre-production schema fixes.
- Keep ordering deterministic by module sequence and timestamp naming.
- Use explicit FK names where cross-module relationships are important.
- Keep monetary fields at `DECIMAL(20,6)` unless higher precision is functionally required.
- Use `row_version` for optimistic concurrency on business aggregates.
- Use `softDeletes()` for transactional lifecycle tables where reversals/auditability matter.

## Recent Standardization Applied

- Added `softDeletes()` to Sales and Purchase transaction tables for reversible lifecycle operations.
- Preserved Audit `tenant_id` as nullable and indexed for compatibility with existing integration test data setup.
- Renamed Inventory valuation-config migration into chronological module order.

## Performance and Consistency Checklist

Before merging any module schema change:

- Verify tenant scoping and indexes include `tenant_id` where required.
- Verify high-cardinality filters have supporting indexes.
- Verify FK delete behavior (`cascadeOnDelete` vs `nullOnDelete`) matches business retention rules.
- Verify no duplicated FK declarations or conflicting constraints.
- Verify naming consistency across table, key, and index names.

## Test Expectations by Risk

High-risk areas that must retain regression coverage:

- Sales/Purchase to Finance posting integration.
- Inventory reservation/allocation/cost-layer correctness.
- Tenant data isolation.
- Tax and pricing resolution under discounts and partial fulfillment.

## Maintenance Guidance

- Extend this document whenever module boundaries or dependencies change.
- Keep architecture guardrail tests updated with new patterns.
- Prefer explicit contracts/events over implicit cross-module coupling.
