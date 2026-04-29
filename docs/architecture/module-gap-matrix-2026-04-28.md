# Module Gap Matrix (2026-04-28)

This matrix summarizes architecture and implementation risk by module, based on code, migration, tests, and contract documentation currently in the repository.

## Scoring Model

- `A`: Mature and low-risk. Contracts, migrations, and tests are aligned.
- `B`: Good coverage, but with moderate technical debt or integration hotspots.
- `C`: Significant risk or known gaps requiring near-term hardening.

## Matrix

| Module | Maturity | Primary Strength | Primary Gap | Priority |
| --- | --- | --- | --- | --- |
| Audit | B | Cross-cutting audit traits and endpoints are present | Retention/archival policy and query scalability should be formalized | Medium |
| Auth | B | Authentication boundary is in place | Deep runtime hardening scenarios not fully centralized in architecture tests | Medium |
| Configuration | A | Strong ownership of reference data (country/currency/language/timezone) | Low-risk maintenance only | Low |
| Core | A | Foundational abstractions and boundary guardrails exist | Continue keeping domain-agnostic scope minimal | Low |
| Customer | B | Solid tenant-scoped customer model and routes | Additional workflow/state invariants can be codified in tests | Medium |
| Employee | B | Module structure and route/test baseline exist | More explicit cross-module HR/Employee contracts would reduce ambiguity | Medium |
| Finance | B | Comprehensive transactional surface and listener integration | High-change posting workflows need continued invariant hardening | High |
| HR | B | Broad process coverage (attendance, payroll, leave, performance) | Status governance and posting boundaries remain sensitive under load | High |
| Inventory | B | Deep integration and valuation/transfer/cycle coverage | Concurrency/idempotency hardening remains a continuous requirement | High |
| OrganizationUnit | B | Strong hierarchy and tenancy integration | Additional integrity/performance checks for large hierarchies recommended | Medium |
| Pricing | B | Pricing resolution patterns and tests present | More formalized conflict-resolution invariants recommended | Medium |
| Product | B | Rich catalog and variant foundations | Stronger catalog-finance traceability contracts recommended | Medium |
| Purchase | B | End-to-end commercial workflow with integrations | Additional workflow replay/idempotency coverage desirable | High |
| Sales | B | End-to-end commercial workflow with integrations | Additional workflow replay/idempotency coverage desirable | High |
| Shared | A | Intentionally minimal shell with clear boundary intent | Keep shell thin and avoid business logic creep | Low |
| Supplier | B | Supplier master and relation surface are in place | More explicit finance/AP integration assertions recommended | Medium |
| Tax | B | Tax entities/rules/resolution and tests exist | Expand runtime tax application edge-case coverage | Medium |
| Tenant | A | Central tenancy model and partitioning strategy are explicit | Ongoing operational governance and lifecycle hardening | Low |
| User | B | RBAC-oriented structures and user support tables are implemented | Permission drift controls and role evolution checks can be expanded | Medium |
| Warehouse | B | Location hierarchy and operations support are implemented | Add more high-volume path performance guardrails | Medium |
| Vehicle | B | Unified rental/service asset lifecycle with dual-status governance is now implemented | Expand deep integration with Finance/Inventory posting and SLA monitoring | High |

## Cross-Module Gap Themes

1. Workflow state governance remains the biggest enterprise risk area in Finance, HR, Inventory, Purchase, and Sales.
2. Concurrency and idempotency hardening is the top runtime resilience requirement in high-volume modules.
3. Cross-module event contracts are present, but invariant tests should continue expanding around replay and ordering edges.
4. Knowledge-base quality is strong; governance now benefits from executable tests that lock key migration/model rules.

## Recommended Next Implementation Wave

1. Add replay/idempotency-focused integration tests for Purchase and Sales payment/return workflows under duplicate submit conditions.
2. Add architecture test gates for expected status transition sets per high-risk transactional module.
3. Add periodic query-shape profiling for inventory/finance high-volume screens and lock required composite indexes.
