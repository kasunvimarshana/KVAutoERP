# Vehicle Module Architecture and Repository Audit (2026-04-29)

## Scope

This audit and implementation cycle covered:

- End-to-end repository architecture review across all modules in `app/Modules`.
- New `Vehicle` bounded context implementation for dual rental/service operations.
- Domain rules for rental/service mutual exclusion.
- API surface, persistence, tenancy, and test baseline for production hardening.

## Repository Architecture Summary

### Established patterns confirmed

- Clean layering (`Domain`, `Application`, `Infrastructure`) is consistently used in implemented modules.
- Tenant isolation is enforced by route middleware (`resolve.tenant`) and repository/model tenant scoping.
- Cross-module synchronization follows event-driven patterns in high-risk modules (Finance, Inventory, Purchase, Sales, HR).
- Core abstractions (`BaseService`, `EloquentRepository`) provide transaction and query composition standards.

### Current architectural pressure points

- High-volume workflows still require continuous replay/idempotency hardening.
- Aggregate line synchronization strategy is not fully uniform in every module.
- Performance guardrails should continue to lock critical composite index/query paths.

## Vehicle Module Overview

### Module boundary

`Vehicle` is a new bounded context for a unified fleet and workshop platform where a vehicle can generate rental revenue and flow through service lifecycle operations.

### Core entities and storage model

- `vehicles`
- `vehicle_documents`
- `vehicle_job_cards`
- `vehicle_service_tasks`
- `vehicle_service_part_usages`
- `vehicle_rentals`

### Key attributes and constraints

- Ownership types: `company_owned`, `third_party_owned`, `customer_owned`, `leased`.
- Identity attributes: VIN, registration number, chassis number, make/model/year.
- Operational statuses:
  - Rental: `available`, `reserved`, `rented`, `blocked`
  - Service: `none`, `in_maintenance`, `under_repair`, `awaiting_parts`, `quality_check`, `ready_for_pickup`, `returned_to_fleet`
- Monetary precision: all financial totals and rates use `DECIMAL(20,6)`.
- Soft deletes applied to key aggregate roots for lifecycle traceability.
- Composite indexes added for tenant/status and expiry lookup paths.

### Business rules implemented

- Vehicle cannot be rented while service workflow is active.
- Vehicle cannot be scheduled for service while reserved/rented.
- Service job-card creation transitions vehicle service status to `in_maintenance`.
- Rental creation transitions vehicle rental status to reservation/rental state.
- Rental close transitions vehicle back to `available` and records return metadata.

### Domain events

- `VehicleRentalStatusChanged`
- `VehicleServiceStatusChanged`

These events establish integration points for downstream accounting, notification, or orchestration flows.

## API Surface

Protected by `auth.configured` and `resolve.tenant`:

- `GET /api/vehicles`
- `POST /api/vehicles`
- `GET /api/vehicles/{vehicle}`
- `DELETE /api/vehicles/{vehicle}`
- `PATCH /api/vehicles/{vehicle}/status`
- `GET /api/vehicles-dashboard`
- `GET /api/vehicles/{vehicle}/job-cards`
- `POST /api/vehicles/job-cards`
- `GET /api/vehicles/{vehicle}/rentals`
- `POST /api/vehicles/rentals`
- `POST /api/vehicles/rentals/{rental}/close`

## Real-time use-case coverage

- Unified registry and lifecycle status visibility.
- Service center flow: job card creation, task and parts capture, service state locking.
- Rental flow: reservation/rental creation, pricing calculation (hourly/daily/weekly/monthly/kilometer), closeout.
- Expiry-alert feed from `vehicle_documents` for compliance monitoring.

## Gaps and next hardening wave

1. Finance integration:
   - Post rental revenue and service labor/parts entries through Finance event listeners.
2. Inventory integration:
   - Enforce stock movement linkage for service parts and reservation guarantees.
3. SLA and bay capacity controls:
   - Add bay-assignment and workshop load-balancing constraints.
4. API documentation automation:
   - Expand OpenAPI generation with endpoint-specific schema annotations.
5. Coverage hardening:
   - Add endpoint-level validation tests and cross-module integration tests.

## Knowledge-base standardization recommendations

1. Keep module-level architecture docs under `docs/architecture` with dated audit files.
2. For every new module, maintain:
   - schema matrix,
   - state-transition matrix,
   - integration-event matrix,
   - route contract tests.
3. Continue architecture guardrails under `tests/Unit/Architecture` for boundary and replay safety.
4. Promote critical invariants from markdown docs into executable tests.
